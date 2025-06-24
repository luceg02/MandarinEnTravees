<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\Reponse;
use App\Entity\User;
use App\Entity\Vote;
use App\Form\DemandeForm;
use App\Form\AnswerForm;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse; //
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/demande')]
class DemandeController extends AbstractController
{
    #[Route('/new', name: 'app_demande_new')]
    #[IsGranted('ROLE_USER')] // Seuls les utilisateurs connectés peuvent accéder
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        DemandeRepository $demandeRepository
    ): Response {
        // Vérification supplémentaire (optionnelle mais recommandée)
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour créer une demande.');
            return $this->redirectToRoute('app_login');
        }

        $demande = new Demande();
        $form = $this->createForm(DemandeForm::class, $demande);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Recherche de doublons potentiels
            //$doublons = $this->rechercherDoublons($demande->getTitre(), $demandeRepository);
            
            // Si des doublons sont trouvés, afficher une alerte
            if (!empty($doublons) && !$request->get('confirmer_soumission')) {
                $this->addFlash('warning', 'Des demandes similaires ont été trouvées. Vérifiez ci-dessous si votre demande n\'existe pas déjà.');
                
                return $this->render('demande/DemandeForm.html.twig', [
                    'form' => $form->createView(),
                    'doublons' => $doublons,
                    'demande' => $demande
                ]);
            }
            
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'), // À définir dans services.yaml
                        $newFilename
                    );
                    
                    // Stocker le nom du fichier dans les liens sources ou créer un champ dédié
                    $liensSources = $demande->getLiensSources();
                    $liensSources .= ($liensSources ? "\n" : '') . "Image: " . $newFilename;
                    $demande->setLiensSources($liensSources);
                    
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }
            
            // Initialiser les valeurs par défaut
            $demande->setDateCreation(new \DateTimeImmutable());
            $demande->setStatut('en_attente');
            $demande->setNbReponses(0);
            
            // CORRECTION : Utiliser l'utilisateur connecté
            $demande->setAuteur($this->getUser());
            
            $entityManager->persist($demande);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre demande de fact-checking a été soumise avec succès !');
            
            return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
        }
        
        return $this->render('demande/DemandeForm.html.twig', [
            'form' => $form->createView(),
            'doublons' => [],
            'demande' => null
        ]);
    }
    
    #[Route('/{id}', name: 'app_demande_detail', requirements: ['id' => '\d+'])]
    public function detail(
        Demande $demande, 
        Request $request, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        // IMPORTANT : Pas de restriction d'accès - tout le monde peut voir la page détaillée
        
        // Créer le formulaire de réponse SEULEMENT pour les utilisateurs connectés
        $form = null;
        
        if ($this->getUser()) {
            $reponse = new Reponse();
            $form = $this->createForm(AnswerForm::class, $reponse);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                // 🆕 RÉCUPÉRER LES DONNÉES DU VOTE DE VÉRACITÉ
                $typeVeracite = $request->request->get('type_veracite');
                $commentaireVeracite = $request->request->get('commentaire_veracite');
                
                // Validation des données obligatoires
                if (empty($typeVeracite)) {
                    $this->addFlash('error', 'L\'évaluation de véracité est obligatoire pour contribuer.');
                    return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
                }
                
                // Vérifier que le type de véracité est valide
                if (!in_array($typeVeracite, [Vote::TYPE_VRAI, Vote::TYPE_FAUX, Vote::TYPE_TROMPEUR, Vote::TYPE_NON_IDENTIFIABLE])) {
                    $this->addFlash('error', 'Type d\'évaluation de véracité invalide.');
                    return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
                }
                
                try {
                    // Gestion de l'upload d'image pour la réponse
                    $imageFile = $form->get('imageFile')->getData();
                    if ($imageFile) {
                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                        try {
                            $imageFile->move(
                                $this->getParameter('images_directory'),
                                $newFilename
                            );
                            
                            $sources = $reponse->getSources();
                            $sources .= ($sources ? "\n" : '') . "Image: " . $newFilename;
                            $reponse->setSources($sources);
                            
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                        }
                    }
                    
                    // 1. CRÉER LA RÉPONSE (EXISTANT)
                    $reponse->setDateCreation(new \DateTimeImmutable());
                    $reponse->setAuteur($this->getUser());
                    $reponse->setDemande($demande);
                    $reponse->setNbVotesPositifs(0);
                    $reponse->setNbVotesNegatifs(0);
                    
                    $entityManager->persist($reponse);
                    
                    // 2. 🆕 CRÉER LE VOTE DE VÉRACITÉ
                    // Vérifier d'abord si l'utilisateur a déjà voté sur cette demande
                    $voteExistant = $entityManager->getRepository(Vote::class)
                        ->findOneBy([
                            'user' => $this->getUser(),
                            'demande' => $demande
                        ]);
                    
                    if ($voteExistant) {
                        // Modifier le vote existant
                        $voteExistant->setTypeVote($typeVeracite);
                        $voteExistant->setCommentaire($commentaireVeracite);
                        $voteExistant->setDateVote(new \DateTimeImmutable());
                    } else {
                        // Créer un nouveau vote de véracité
                        $voteVeracite = new Vote();
                        $voteVeracite->setUser($this->getUser());
                        $voteVeracite->setDemande($demande);        // ✅ Vote lié à la demande
                        $voteVeracite->setReponse(null);            // ✅ Pas lié à une réponse
                        $voteVeracite->setTypeVote($typeVeracite);
                        $voteVeracite->setCommentaire($commentaireVeracite);
                        $voteVeracite->setDateVote(new \DateTimeImmutable());
                        
                        $entityManager->persist($voteVeracite);
                    }
                    
                    // 3. 🆕 RECALCULER LE VERDICT DE LA DEMANDE
                    $demande->calculerVerdictAutomatique();
                    
                    // 4. METTRE À JOUR LES DONNÉES DE LA DEMANDE
                    $demande->setNbReponses($demande->getNbReponses() + 1);
                    $demande->setDateModification(new \DateTimeImmutable());
                    
                    // Si c'est la première réponse, changer le statut
                    if ($demande->getStatut() === 'en_attente') {
                        $demande->setStatut('en_cours');
                    }
                    
                    // 5. SAUVEGARDER TOUT
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Votre contribution et votre évaluation ont été enregistrées avec succès !');
                    
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
                }
                
                return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
            }
        }
        
        return $this->render('demande/detail.html.twig', [
            'demande' => $demande,
            'form' => $form?->createView()
        ]);
    }
    
    #[Route('/liste', name: 'app_demande_liste')]
    public function liste(DemandeRepository $demandeRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20; // 20 éléments par page comme spécifié dans le cahier des charges
        
        $demandes = $demandeRepository->findBy(
            [],
            ['dateCreation' => 'DESC'],
            $limit,
            ($page - 1) * $limit
        );
        
        return $this->render('demande/liste.html.twig', [
            'demandes' => $demandes,
            'page' => $page
        ]);
    }
    
    /**
     * 🆕 NOUVELLE ROUTE : Voter sur la véracité d'une demande (via AJAX)
     * Alternative au système intégré dans le formulaire de réponse
     */
    #[Route('/{id}/vote-veracite', name: 'app_demande_vote_veracite', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function voterVeraciteAjax(
        Demande $demande, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response {
        $typeVote = $request->request->get('type_veracite');
        $commentaire = $request->request->get('commentaire_veracite');

        // Validation
        if (!in_array($typeVote, [Vote::TYPE_VRAI, Vote::TYPE_FAUX, Vote::TYPE_TROMPEUR, Vote::TYPE_NON_IDENTIFIABLE])) {
            $this->addFlash('error', 'Type de vote invalide.');
            return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
        }

        try {
            $currentUser = $this->getUser();

            // Vérifier si l'utilisateur a déjà voté
            $voteExistant = $entityManager->getRepository(Vote::class)
                ->findOneBy([
                    'user' => $currentUser,
                    'demande' => $demande
                ]);

            if ($voteExistant) {
                // Modifier le vote existant
                $voteExistant->setTypeVote($typeVote);
                $voteExistant->setCommentaire($commentaire);
                $voteExistant->setDateVote(new \DateTimeImmutable());
                $message = 'Votre évaluation a été modifiée.';
            } else {
                // Créer un nouveau vote
                $vote = new Vote();
                $vote->setUser($currentUser);
                $vote->setDemande($demande);
                $vote->setReponse(null);
                $vote->setTypeVote($typeVote);
                $vote->setCommentaire($commentaire);
                $vote->setDateVote(new \DateTimeImmutable());
                
                $entityManager->persist($vote);
                $message = 'Votre évaluation a été enregistrée.';
            }

            // Recalculer le verdict automatique
            $demande->calculerVerdictAutomatique();
            
            $entityManager->flush();
            
            $this->addFlash('success', $message);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'enregistrement du vote.');
        }

        return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
    }
    
    /**
     * Recherche des doublons potentiels basés sur les mots-clés du titre
     */
    private function rechercherDoublons(string $titre, DemandeRepository $demandeRepository): array
    {
        // Extraire les mots-clés du titre (mots de plus de 3 caractères)
        $mots = array_filter(
            explode(' ', strtolower($titre)), 
            fn($mot) => strlen(trim($mot, '.,!?;:')) > 3
        );
        
        if (empty($mots)) {
            return [];
        }
        
        // Rechercher des demandes contenant ces mots-clés
        return $demandeRepository->rechercherParMotsCles($mots, 5); // Limite à 5 résultats
    }
    /** 🗑️ SUPPRESSION D'UNE RÉPONSE (COMMENTAIRE) */
    #[Route('/reponse/{id}/supprimer', name: 'app_reponse_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function supprimerReponse(
        Reponse $reponse, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response {
        $currentUser = $this->getUser();
        
        // 🔒 VÉRIFICATION D'AUTORISATION
        if ($reponse->getAuteur() !== $currentUser) {
            $this->addFlash('error', 'Vous ne pouvez supprimer que vos propres commentaires.');
            return $this->redirectToRoute('app_demande_detail', ['id' => $reponse->getDemande()->getId()]);
        }
        
        // 🛡️ PROTECTION CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('supprimer_reponse_' . $reponse->getId(), $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_demande_detail', ['id' => $reponse->getDemande()->getId()]);
        }
        
        try {
            $demande = $reponse->getDemande();
            
            // 📊 MISE À JOUR DU COMPTEUR DE LA DEMANDE
            $demande->setNbReponses(max(0, $demande->getNbReponses() - 1));
            $demande->setDateModification(new \DateTimeImmutable());
            
            // 🔄 RECALCULER LE VERDICT (au cas où cette réponse avait un vote de véracité)
            $demande->calculerVerdictAutomatique();
            
            // 🗑️ SUPPRESSION DE LA RÉPONSE
            // Doctrine supprime automatiquement les votes liés (cascade: ['remove'])
            $entityManager->remove($reponse);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre commentaire a été supprimé avec succès.');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression du commentaire.');
        }
        
        return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
    }
    /**
     * 🗑️ SUPPRESSION D'UNE DEMANDE
     * Seul l'auteur de la demande peut la supprimer
     */
    #[Route('/{id}/supprimer', name: 'app_demande_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function supprimerDemande(
        Demande $demande, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response {
        $currentUser = $this->getUser();
        
        // 🔒 VÉRIFICATION D'AUTORISATION
        if ($demande->getAuteur() !== $currentUser) {
            $this->addFlash('error', 'Vous ne pouvez supprimer que vos propres demandes.');
            return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
        }
        
        // 🛡️ PROTECTION CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('supprimer_demande_' . $demande->getId(), $token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
        }
        
        // ⚠️ VÉRIFICATION SUPPLÉMENTAIRE : Empêcher la suppression si beaucoup de contributions
        if ($demande->getNbReponses() > 5) {
            $this->addFlash('warning', 
                'Cette demande a déjà reçu ' . $demande->getNbReponses() . ' contributions. ' .
                'Pour préserver le travail de la communauté, elle ne peut plus être supprimée. ' .
                'Contactez un administrateur si nécessaire.'
            );
            return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
        }
        
        try {
            // 📊 STATISTIQUES POUR LE MESSAGE DE CONFIRMATION
            $nbReponses = $demande->getNbReponses();
            $nbVotes = $demande->getVotesVeracite()->count();
            $titreDemande = $demande->getTitre();
            
            // 🗑️ SUPPRESSION EN CASCADE
            // Doctrine va automatiquement supprimer :
            // - Les réponses liées (OneToMany avec cascade)
            // - Les votes liés aux réponses (OneToMany avec cascade: ['remove'])
            // - Les votes de véracité sur la demande (OneToMany avec cascade)
            
            $entityManager->remove($demande);
            $entityManager->flush();
            
            // 📄 MESSAGE DE SUCCÈS DÉTAILLÉ
            if ($nbReponses > 0 || $nbVotes > 0) {
                $this->addFlash('success', 
                    "La demande \"{$titreDemande}\" a été supprimée avec succès, " .
                    "ainsi que {$nbReponses} contribution(s) et {$nbVotes} évaluation(s) associées."
                );
            } else {
                $this->addFlash('success', 
                    "La demande \"{$titreDemande}\" a été supprimée avec succès."
                );
            }
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression de la demande : ' . $e->getMessage());
            return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
        }
        
        // 🏠 REDIRECTION VERS L'ACCUEIL
        return $this->redirectToRoute('app_home');
    }
}