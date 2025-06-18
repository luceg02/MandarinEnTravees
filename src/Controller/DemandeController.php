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
        SluggerInterface $slugger
    ): Response {
        // Vérifier que l'utilisateur est connecté
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour soumettre une demande.');
            return $this->redirectToRoute('app_login');
        }
        
        $demande = new Demande();
        $form = $this->createForm(DemandeForm::class, $demande);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    // S'assurer que le dossier existe
                    $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
                    if (!is_dir($uploadsDirectory)) {
                        mkdir($uploadsDirectory, 0755, true);
                    }
                    
                    $imageFile->move($uploadsDirectory, $newFilename);
                    
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
            
            // Récupérer l'utilisateur connecté
            $user = $this->getUser();
            if ($user instanceof User) {
                $demande->setAuteur($user);
            }
            
            $entityManager->persist($demande);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre demande de fact-checking a été soumise avec succès !');
            
            return $this->redirectToRoute('app_demande_detail', ['id' => $demande->getId()]);
        }
        
        return $this->render('demande/nouvelle.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/{id}', name: 'app_demande_detail', requirements: ['id' => '\d+'])]
    public function detail(Demande $demande): Response
    {
        return $this->render('demande/detail.html.twig', [
            'demande' => $demande
        ]);
    }
}