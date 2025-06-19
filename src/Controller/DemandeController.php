<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\Reponse;
use App\Entity\User;
use App\Form\DemandeForm;
use App\Form\AnswerForm;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
                
                // Initialiser la réponse
                $reponse->setDateCreation(new \DateTimeImmutable());
                $reponse->setAuteur($this->getUser());
                $reponse->setDemande($demande);
                $reponse->setNbVotesPositifs(0);
                $reponse->setNbVotesNegatifs(0);
                
                // Mettre à jour le nombre de réponses de la demande
                $demande->setNbReponses($demande->getNbReponses() + 1);
                $demande->setDateModification(new \DateTimeImmutable());
                
                // Si c'est la première réponse, changer le statut
                if ($demande->getStatut() === 'en_attente') {
                    $demande->setStatut('en_cours');
                }
                
                $entityManager->persist($reponse);
                $entityManager->persist($demande);
                $entityManager->flush();
                
                $this->addFlash('success', 'Votre contribution a été ajoutée avec succès !');
                
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
}