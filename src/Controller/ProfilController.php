<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DemandeRepository;
use App\Repository\ReponseRepository;
use App\Repository\VoteRepository; // Ajout du repository Vote
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
        ReponseRepository $reponseRepository,
        VoteRepository $voteRepository // Ajout du repository Vote
    ): Response {
        $onglet = $request->query->get('onglet', 'tout');
        
        // Calculer les statistiques de l'utilisateur
        $nbReponses = $reponseRepository->count(['auteur' => $user]);
        $nbDemandes = $demandeRepository->count(['auteur' => $user]);
        
        // Calculer le total des upvotes pour l'utilisateur
        $totalUpvotes = $voteRepository->getTotalUpvotesForUser($user);
        
        // Initialiser TOUTES les variables pour éviter les erreurs
        $demandes = [];
        $reponses = [];
        $items = [];
        
        switch ($onglet) {
            case 'demandes':
                $demandes = $demandeRepository->findBy(
                    ['auteur' => $user],
                    ['dateCreation' => 'DESC']
                );
                $items = $demandes;
                break;
                
            case 'verifications':
                $reponses = $reponseRepository->findBy(
                    ['auteur' => $user],
                    ['dateCreation' => 'DESC']
                );
                
                // Ajouter les upvotes pour chaque réponse
                foreach ($reponses as $reponse) {
                    $reponse->upvotes = $voteRepository->countUpvotesForReponse($reponse);
                }
                
                $items = $reponses;
                break;
                
            default: // 'tout'
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
                
                // Ajouter les upvotes pour chaque réponse
                foreach ($reponses as $reponse) {
                    $reponse->upvotes = $voteRepository->countUpvotesForReponse($reponse);
                }
                
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
            'nbReponses' => $nbReponses,
            'nbDemandes' => $nbDemandes,
            'totalUpvotes' => $totalUpvotes, // Ajouter le total des upvotes
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