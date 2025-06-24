<?php

namespace App\Controller;

use App\Repository\DemandeRepository;
use App\Repository\UserRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/categorie/{categorie}', name: 'app_demandes_par_categorie')]
    public function demandesParCategorie(
        string $categorie,
        Request $request,
        DemandeRepository $demandeRepository,
        UserRepository $userRepository,
        CategorieRepository $categorieRepository
    ): Response {
        // Mapping des catégories URL vers les vraies valeurs
        $categoriesMap = [
            'politique' => 'Politique',
            'sante' => 'Santé',
            'economie' => 'Économie',
            'societe' => 'Société',
            'tech' => 'Tech'
        ];

        // Vérifier que la catégorie existe
        if (!array_key_exists($categorie, $categoriesMap)) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        $categorieNom = $categoriesMap[$categorie];
        
        // Trouver l'entité Categorie par nom
        $categorieEntity = $categorieRepository->findOneBy(['nom' => $categorieNom]);
        
        if (!$categorieEntity) {
            throw $this->createNotFoundException('Catégorie non trouvée en base de données');
        }

        // Paramètres de pagination
        $page = $request->query->getInt('page', 1);
        $limit = 15;
        $offset = ($page - 1) * $limit;

        // Récupérer les demandes de cette catégorie (par ID de catégorie)
        $demandes = $demandeRepository->findBy(
            ['categorie' => $categorieEntity], // Utiliser l'entité au lieu du nom
            ['dateCreation' => 'DESC'],
            $limit,
            $offset
        );

        // Compter le total pour cette catégorie
        $totalDemandes = $demandeRepository->count(['categorie' => $categorieEntity]);
        $totalPages = ceil($totalDemandes / $limit);

        // Top contributeurs (même logique que homepage)
        $topContributeurs = $userRepository->findTopContributeursByReponses(5);

        // Statistiques générales (même logique que homepage)
        $statsGenerales = [
            'totalDemandes' => $demandeRepository->count([]),
            'totalContributeursActifs' => $userRepository->countContributeursActifs()
        ];

        return $this->render('category/index.html.twig', [
            'categorie' => $categorieNom,
            'categorieSlug' => $categorie,
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