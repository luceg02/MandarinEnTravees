<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\User;
use App\Form\DemandeForm;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/demande')]
class DemandeController extends AbstractController
{
    #[Route('/nouvelle', name: 'app_demande_nouvelle')]
    public function nouvelle(
        Request $request, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        DemandeRepository $demandeRepository
    ): Response {
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
            
            // SOLUTION TEMPORAIRE : prendre le premier utilisateur en base
            $premierUser = $entityManager->getRepository(User::class)->findOneBy([]);
            if ($premierUser) {
                $demande->setAuteur($premierUser);
            } else {
                throw new \Exception('Aucun utilisateur en base pour assigner la demande');
            }
            
            $entityManager->persist($demande);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre demande de fact-checking a été soumise avec succès !');
            
            //return $this->redirectToRoute('app_home', ['id' => $demande->getId()]);
            return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);

        }
        
        return $this->render('demande/DemandeForm.html.twig', [
            'form' => $form->createView(),
            'doublons' => [],
            'demande' => null
        ]);
    }
    
    #[Route('/{id}', name: 'app_demande_detail', requirements: ['id' => '\d+'])]
    public function detail(Demande $demande): Response
    {
        return $this->render('demande/detail.html.twig', [
            'demande' => $demande
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