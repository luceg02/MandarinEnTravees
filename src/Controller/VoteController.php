<?php
// src/Controller/VoteController.php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\User;
use App\Entity\Reponse;
use App\Repository\VoteRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/vote')]
class VoteController extends AbstractController
{
    #[Route('/reponse/{reponseId}/{typeVote}', name: 'app_vote_reponse', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function voterReponse(
        int $reponseId,
        string $typeVote,
        Request $request,
        EntityManagerInterface $em,
        ReponseRepository $reponseRepo,
        VoteRepository $voteRepo
    ): JsonResponse {
        
        // Vérifier que le type de vote est valide
        if (!in_array($typeVote, ['utile', 'pas_utile'])) {
            return new JsonResponse(['error' => 'Type de vote invalide'], 400);
        }
        
        // Récupérer la réponse
        $reponse = $reponseRepo->find($reponseId);
        if (!$reponse) {
            return new JsonResponse(['error' => 'Réponse non trouvée'], 404);
        }
        
        // Empêcher de voter sur sa propre réponse
        if ($reponse->getAuteur() === $this->getUser()) {
            return new JsonResponse(['error' => 'Vous ne pouvez pas voter sur votre propre réponse'], 403);
        }
        
        // Vérifier si l'utilisateur a déjà voté (approche manuelle)
        $voteExistant = $this->verifierVoteExistant($this->getUser()->getId(), $reponseId, $voteRepo);
        
        if ($voteExistant) {
            // Supprimer l'ancien vote et ses compteurs
            $this->supprimerVote($voteExistant, $reponse, $em);
        }
        
        // Créer le nouveau vote
        $vote = new Vote();
        $vote->setTypeVote($typeVote);
        $vote->setDateVote(new \DateTimeImmutable());
        $vote->setCommentaire("user_id:{$this->getUser()->getId()},reponse_id:{$reponseId}"); // HACK temporaire
        
        // Mettre à jour les compteurs de la réponse
        if ($typeVote === 'utile') {
            $reponse->setNbVotesPositifs($reponse->getNbVotesPositifs() + 1);
        } else {
            $reponse->setNbVotesNegatifs($reponse->getNbVotesNegatifs() + 1);
        }
        
        $em->persist($vote);
        $em->persist($reponse);
        $em->flush();
        
        return new JsonResponse([
            'success' => true,
            'nbVotesPositifs' => $reponse->getNbVotesPositifs(),
            'nbVotesNegatifs' => $reponse->getNbVotesNegatifs(),
            'message' => 'Vote enregistré avec succès'
        ]);
    }
    
    // HACK temporaire pour vérifier les votes existants
    private function verifierVoteExistant(int $userId, int $reponseId, VoteRepository $voteRepo): ?Vote
    {
        $votes = $voteRepo->findAll();
        foreach ($votes as $vote) {
            $commentaire = $vote->getCommentaire();
            if (str_contains($commentaire, "user_id:{$userId}") && 
                str_contains($commentaire, "reponse_id:{$reponseId}")) {
                return $vote;
            }
        }
        return null;
    }
    
    private function supprimerVote(Vote $vote, Reponse $reponse, EntityManagerInterface $em): void
    {
        // Décrémenter les compteurs
        if ($vote->getTypeVote() === 'utile') {
            $reponse->setNbVotesPositifs(max(0, $reponse->getNbVotesPositifs() - 1));
        } else {
            $reponse->setNbVotesNegatifs(max(0, $reponse->getNbVotesNegatifs() - 1));
        }
        
        $em->remove($vote);
    }
}