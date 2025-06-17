<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        // Récupération des catégories depuis ta BD
        $categories = $categorieRepository->findAll();

        // Données fictives temporaires pour le reste
        $demandes = [
            [
                'id' => 1,
                'titre' => 'Le gouvernement a-t-il vraiment augmenté les impôts de 15% cette année ?',
                'description' => 'Après vérification des données officielles de Bercy...',
                'statut' => 'vrai',
                'categorie' => ['nom' => 'Politique'],
                'auteur' => ['username' => 'Pierre Martin'],
                'nbReponses' => 8,
                'dateCreation' => new \DateTime('-2 hours')
            ]
        ];

        return $this->render('homepage.html.twig', [
            'categories' => $categories,
            'demandes' => $demandes
        ]);
    }
}