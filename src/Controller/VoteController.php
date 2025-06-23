<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\Reponse;
use App\Entity\Vote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class VoteController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // ========================================================================
    // SYSTÈME 1 : VOTES D'UTILITÉ SUR LES RÉPONSES (EXISTANT AMÉLIORÉ)
    // ========================================================================

    #[Route('/vote/reponse/{id}/{typeVote}', name: 'vote_reponse', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function voterReponse(Reponse $reponse, string $typeVote): JsonResponse
    {
        // Vérifier que le type de vote est valide
        if (!in_array($typeVote, [Vote::TYPE_UTILE, Vote::TYPE_PAS_UTILE])) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Type de vote invalide'
            ], 400);
        }

        $currentUser = $this->getUser();

        // Vérifier que l'utilisateur ne vote pas pour sa propre réponse
        if ($reponse->getAuteur() === $currentUser) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Vous ne pouvez pas voter pour votre propre réponse'
            ], 403);
        }

        try {
            // Vérifier si l'utilisateur a déjà voté pour cette réponse
            $voteExistant = $this->entityManager->getRepository(Vote::class)
                ->findOneBy([
                    'user' => $currentUser,
                    'reponse' => $reponse
                ]);

            if ($voteExistant) {
                // Si c'est le même type de vote, on l'annule
                if ($voteExistant->getTypeVote() === $typeVote) {
                    return $this->annulerVoteReponse($voteExistant, $reponse);
                } else {
                    // Sinon on change le vote
                    return $this->changerVoteReponse($voteExistant, $typeVote, $reponse);
                }
            } else {
                // Nouveau vote
                return $this->creerNouveauVoteReponse($currentUser, $reponse, $typeVote);
            }

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Erreur lors du vote'
            ], 500);
        }
    }

    private function creerNouveauVoteReponse($user, Reponse $reponse, string $typeVote): JsonResponse
    {
        // Créer le vote (avec reponse_id, demande_id = null)
        $vote = new Vote();
        $vote->setUser($user);
        $vote->setReponse($reponse);        // ✅ Vote lié à la réponse
        $vote->setDemande(null);            // ✅ Pas lié à une demande
        $vote->setTypeVote($typeVote);
        $vote->setDateVote(new \DateTimeImmutable());

        $this->entityManager->persist($vote);

        // Mettre à jour les compteurs
        if ($typeVote === Vote::TYPE_UTILE) {
            $reponse->setNbVotesPositifs($reponse->getNbVotesPositifs() + 1);
        } else {
            $reponse->setNbVotesNegatifs($reponse->getNbVotesNegatifs() + 1);
        }

        // 🎯 CALCUL AUTOMATIQUE DU SCORE (LES 3 LIGNES IMPORTANTES)
        $auteurReponse = $reponse->getAuteur();
        $auteurReponse->mettreAJourScore();
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Vote enregistré',
            'nbVotesPositifs' => $reponse->getNbVotesPositifs(),
            'nbVotesNegatifs' => $reponse->getNbVotesNegatifs()
        ]);
    }

    private function changerVoteReponse(Vote $vote, string $nouveauTypeVote, Reponse $reponse): JsonResponse
    {
        $ancienTypeVote = $vote->getTypeVote();

        // Retirer l'ancien vote des compteurs
        if ($ancienTypeVote === Vote::TYPE_UTILE) {
            $reponse->setNbVotesPositifs($reponse->getNbVotesPositifs() - 1);
        } else {
            $reponse->setNbVotesNegatifs($reponse->getNbVotesNegatifs() - 1);
        }

        // Ajouter le nouveau vote aux compteurs
        if ($nouveauTypeVote === Vote::TYPE_UTILE) {
            $reponse->setNbVotesPositifs($reponse->getNbVotesPositifs() + 1);
        } else {
            $reponse->setNbVotesNegatifs($reponse->getNbVotesNegatifs() + 1);
        }

        // Mettre à jour le vote
        $vote->setTypeVote($nouveauTypeVote);
        $vote->setDateVote(new \DateTimeImmutable());

        // 🎯 CALCUL AUTOMATIQUE DU SCORE
        $auteurReponse = $reponse->getAuteur();
        $auteurReponse->mettreAJourScore();
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Vote modifié',
            'nbVotesPositifs' => $reponse->getNbVotesPositifs(),
            'nbVotesNegatifs' => $reponse->getNbVotesNegatifs()
        ]);
    }

    private function annulerVoteReponse(Vote $vote, Reponse $reponse): JsonResponse
    {
        $typeVote = $vote->getTypeVote();

        // Retirer le vote des compteurs
        if ($typeVote === Vote::TYPE_UTILE) {
            $reponse->setNbVotesPositifs($reponse->getNbVotesPositifs() - 1);
        } else {
            $reponse->setNbVotesNegatifs($reponse->getNbVotesNegatifs() - 1);
        }

        // 🎯 CALCUL AUTOMATIQUE DU SCORE
        $auteurReponse = $reponse->getAuteur();
        $auteurReponse->mettreAJourScore();
        
        // Supprimer le vote
        $this->entityManager->remove($vote);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Vote annulé',
            'nbVotesPositifs' => $reponse->getNbVotesPositifs(),
            'nbVotesNegatifs' => $reponse->getNbVotesNegatifs()
        ]);
    }

    // ========================================================================
    // 🆕 SYSTÈME 2 : VOTES DE VÉRACITÉ SUR LES DEMANDES
    // ========================================================================

    #[Route('/vote/demande/{id}/veracite', name: 'vote_demande_veracite', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function voterVeracite(Demande $demande, Request $request): JsonResponse
    {
        $typeVote = $request->request->get('type_veracite');
        $commentaire = $request->request->get('commentaire_veracite');

        // Vérifier que le type de vote est valide
        if (!in_array($typeVote, [Vote::TYPE_VRAI, Vote::TYPE_FAUX, Vote::TYPE_TROMPEUR, Vote::TYPE_NON_IDENTIFIABLE])) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Type de vote de véracité invalide'
            ], 400);
        }

        $currentUser = $this->getUser();

        try {
            // Vérifier si l'utilisateur a déjà voté sur cette demande
            $voteExistant = $this->entityManager->getRepository(Vote::class)
                ->findOneBy([
                    'user' => $currentUser,
                    'demande' => $demande
                ]);

            if ($voteExistant) {
                // Modifier le vote existant
                return $this->modifierVoteVeracite($voteExistant, $typeVote, $commentaire, $demande);
            } else {
                // Créer un nouveau vote de véracité
                return $this->creerNouveauVoteVeracite($currentUser, $demande, $typeVote, $commentaire);
            }

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Erreur lors du vote de véracité: ' . $e->getMessage()
            ], 500);
        }
    }

    private function creerNouveauVoteVeracite($user, Demande $demande, string $typeVote, ?string $commentaire): JsonResponse
    {
        // Créer le vote (avec demande_id, reponse_id = null)
        $vote = new Vote();
        $vote->setUser($user);
        $vote->setDemande($demande);        // ✅ Vote lié à la demande
        $vote->setReponse(null);            // ✅ Pas lié à une réponse
        $vote->setTypeVote($typeVote);
        $vote->setCommentaire($commentaire);
        $vote->setDateVote(new \DateTimeImmutable());

        $this->entityManager->persist($vote);

        // 🎯 RECALCULER LE VERDICT AUTOMATIQUE DE LA DEMANDE
        $demande->calculerVerdictAutomatique();
        
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Vote de véracité enregistré',
            'vote' => [
                'type' => $vote->getLibelleVote(),
                'couleur' => $vote->getCouleurVote(),
                'icone' => $vote->getIconeVote(),
                'commentaire' => $vote->getCommentaire()
            ],
            'verdict' => [
                'verdict' => $demande->getVerdictAutomatique(),
                'confiance' => $demande->getScoreConfiance(),
                'libelle' => $demande->getLibelleVerdict()
            ]
        ]);
    }

    private function modifierVoteVeracite(Vote $vote, string $nouveauTypeVote, ?string $commentaire, Demande $demande): JsonResponse
    {
        // Mettre à jour le vote existant
        $vote->setTypeVote($nouveauTypeVote);
        $vote->setCommentaire($commentaire);
        $vote->setDateVote(new \DateTimeImmutable());

        // 🎯 RECALCULER LE VERDICT AUTOMATIQUE DE LA DEMANDE
        $demande->calculerVerdictAutomatique();
        
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Vote de véracité modifié',
            'vote' => [
                'type' => $vote->getLibelleVote(),
                'couleur' => $vote->getCouleurVote(),
                'icone' => $vote->getIconeVote(),
                'commentaire' => $vote->getCommentaire()
            ],
            'verdict' => [
                'verdict' => $demande->getVerdictAutomatique(),
                'confiance' => $demande->getScoreConfiance(),
                'libelle' => $demande->getLibelleVerdict()
            ]
        ]);
    }
}