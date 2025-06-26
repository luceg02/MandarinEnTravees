<?php
namespace App\Controller;

use App\Repository\DemandeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request, 
        DemandeRepository $demandeRepository,
        UserRepository $userRepository
    ): Response {
        $categories = [
            (object)['nom' => 'Politique'],
            (object)['nom' => 'Santé'],
            (object)['nom' => 'Économie'],
            (object)['nom' => 'Société'],
            (object)['nom' => 'Tech']
        ];
       
        // Paramètres de pagination
        $page = $request->query->getInt('page', 1);
        $limit = 15; 
        $offset = ($page - 1) * $limit;
       
        // Récupérer les demandes avec pagination
        $demandes = $demandeRepository->findBy(
            [], 
            ['dateCreation' => 'DESC'], 
            $limit, 
            $offset 
        );
       
        $totalDemandes = $demandeRepository->count([]);
        $totalPages = ceil($totalDemandes / $limit);

        // Top contributeurs - utilisateurs avec le plus de réponses
        $topContributeurs = $userRepository->findTopContributeursByReponses(5);

        // Statistiques générales
        $statsGenerales = [
            'totalDemandes' => $totalDemandes,
            'totalContributeursActifs' => $userRepository->countContributeursActifs()
        ];
       
        return $this->render('home/index.html.twig', [
            'categories' => $categories,
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