<?php

namespace App\Controller;

use App\Repository\DemandeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/recherche', name: 'app_recherche')]
    public function recherche(
        Request $request,
        DemandeRepository $demandeRepository,
        UserRepository $userRepository
    ): Response {
        $query = $request->query->get('q', '');
        
        // Si pas de recherche, rediriger vers l'accueil
        if (empty(trim($query))) {
            return $this->redirectToRoute('app_home');
        }

        // Paramètres de pagination
        $page = $request->query->getInt('page', 1);
        $limit = 15;

        // Rechercher les demandes
        $demandes = $demandeRepository->rechercherDemandes($query, $page, $limit);
        $totalDemandes = $demandeRepository->compterResultatsRecherche($query);
        $totalPages = ceil($totalDemandes / $limit);

        // Top contributeurs (même logique que homepage)
        $topContributeurs = $userRepository->findTopContributeursByReponses(5);

        // Statistiques générales
        $statsGenerales = [
            'totalDemandes' => $demandeRepository->count([]),
            'totalContributeursActifs' => $userRepository->countContributeursActifs()
        ];

        return $this->render('search/index.html.twig', [
            'query' => $query,
            'demandes' => $demandes,
            'page_courante' => $page,
            'total_pages' => $totalPages,
            'total_demandes' => $totalDemandes,
            'limite_par_page' => $limit,
            'topContributeurs' => $topContributeurs,
            'statsGenerales' => $statsGenerales,
        ]);
    }
}