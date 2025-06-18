<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\DemandeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(
        Request $request,
        CategorieRepository $categorieRepository,
        DemandeRepository $demandeRepository,
        UserRepository $userRepository
    ): Response {
        // Récupération des catégories depuis la BD
        $categories = $categorieRepository->findAll();

        // Paramètres de pagination
        $page = $request->query->getInt('page', 1);
        $limit = 10; // 10 demandes par page
        $offset = ($page - 1) * $limit;
        
        // Récupérer les demandes de façon simple
        $demandes = $demandeRepository->findBy(
            [], // critères (aucun = toutes)
            ['dateCreation' => 'DESC'], // tri par date décroissante
            $limit, // limite
            $offset // décalage
        );
        
        // Compter le total pour la pagination
        $totalDemandes = $demandeRepository->count([]);
        $totalPages = ceil($totalDemandes / $limit);
        
        // Top contributeurs (utilisateurs les plus récents pour le moment)
        $topContributeurs = $userRepository->findBy(
            [], // Pas de filtre
            ['dateInscription' => 'DESC'], // Tri par date d'inscription
            5 // Top 5
        );
        
        // Ajouter des scores fictifs aux contributeurs pour l'affichage
        $fakeScores = [1200, 980, 1500, 750, 2100];
        foreach ($topContributeurs as $index => $contributeur) {
            $contributeur->fakeScore = $fakeScores[$index] ?? rand(100, 2000);
        }
        
        // Trier par score fictif décroissant
        usort($topContributeurs, function($a, $b) {
            return $b->fakeScore - $a->fakeScore;
        });
        
        // Statistiques simples
        $totalUtilisateurs = $userRepository->count([]);
        
        $stats = [
            'totalDemandes' => $totalDemandes,
            'totalUtilisateurs' => $totalUtilisateurs
        ];

        return $this->render('homepage.html.twig', [
            'categories' => $categories,
            'demandes' => $demandes,
            'topContributeurs' => $topContributeurs,
            'stats' => $stats,
            'page_courante' => $page,
            'total_pages' => $totalPages,
            'total_demandes' => $totalDemandes
        ]);
    }
}