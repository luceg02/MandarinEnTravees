<?php
namespace App\Controller;

use App\Repository\DemandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, DemandeRepository $demandeRepository): Response
    {
        $categories = [
            (object)['nom' => 'Politique'],
            (object)['nom' => 'Santé'],
            (object)['nom' => 'Économie'],
            (object)['nom' => 'Société'],
            (object)['nom' => 'Tech']
        ];
        
        // Paramètres de pagination
        $page = $request->query->getInt('page', 1);
        $limit = 15; // 15 demandes par page
        $offset = ($page - 1) * $limit;
       
        // Récupérer les demandes avec pagination
        $demandes = $demandeRepository->findBy(
            [], // critères (aucun = toutes)
            ['dateCreation' => 'DESC'], // tri par date décroissante
            $limit, // limite
            $offset // décalage
        );
       
        // Compter le total pour la pagination
        $totalDemandes = $demandeRepository->count([]);
        $totalPages = ceil($totalDemandes / $limit);
       
        return $this->render('home/index.html.twig', [
            'categories' => $categories,
            'demandes' => $demandes,
            'page_courante' => $page,
            'total_pages' => $totalPages,
            'total_demandes' => $totalDemandes,
            'limite_par_page' => $limit
        ]);
    }
}