<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DemandeRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil/{id}', name: 'app_profil', requirements: ['id' => '\d+'])]
    public function index(
        User $user,
        Request $request,
        DemandeRepository $demandeRepository,
        ReponseRepository $reponseRepository
    ): Response {
        $onglet = $request->query->get('onglet', 'tout');
        
        // Initialiser TOUTES les variables pour éviter les erreurs
        $demandes = [];
        $reponses = [];
        $items = [];
        
        switch ($onglet) {
            case 'demandes':
                // Simplifier la requête pour éviter l'erreur Doctrine
                $demandes = $demandeRepository->findBy(
                    ['auteur' => $user],
                    ['dateCreation' => 'DESC']
                );
                $items = $demandes;
                break;
                
            case 'verifications':
                // Simplifier la requête pour éviter l'erreur Doctrine
                $reponses = $reponseRepository->findBy(
                    ['auteur' => $user],
                    ['dateCreation' => 'DESC']
                );
                $items = $reponses;
                break;
                
            default: // 'tout'
                // Simplifier les requêtes pour éviter l'erreur Doctrine
                $demandes = $demandeRepository->findBy(
                    ['auteur' => $user],
                    ['dateCreation' => 'DESC'],
                    10
                );
                    
                $reponses = $reponseRepository->findBy(
                    ['auteur' => $user],
                    ['dateCreation' => 'DESC'],
                    10
                );
                
                // Mélanger et trier par date
                $items = array_merge($demandes, $reponses);
                usort($items, function($a, $b) {
                    return $b->getDateCreation() <=> $a->getDateCreation();
                });
                break;
        }

        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'onglet' => $onglet,
            'items' => $items ?? [],
            'demandes' => $demandes ?? [],
            'reponses' => $reponses ?? [],
        ]);
    }

    #[Route('/mon-profil', name: 'app_mon_profil')]
    public function monProfil(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);
    }
}