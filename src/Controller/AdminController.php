<?php
// src/Controller/AdminController.php

namespace App\Controller;

use App\Entity\Report;
use App\Entity\User;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use App\Repository\DemandeRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ReportRepository $reportRepository,
        private UserRepository $userRepository,
        private DemandeRepository $demandeRepository,
        private ReponseRepository $reponseRepository
    ) {}

    #[Route('/', name: 'admin_dashboard')]
public function dashboard(): Response
{
    // Statistiques
    $totalUsers = $this->userRepository->count([]);
    
    // Journalistes validés (avec ROLE_JOURNALISTE et statut_validation = 'valide')
    $journalistesValides = $this->userRepository->createQueryBuilder('u')
        ->select('COUNT(u.id)')
        ->where('u.roles LIKE :role')
        ->andWhere('u.statut_validation = :statut')
        ->setParameter('role', '%ROLE_JOURNALISTE%')
        ->setParameter('statut', 'valide')
        ->getQuery()
        ->getSingleScalarResult();
    
    // Journalistes en attente (avec ROLE_JOURNALISTE et statut_validation = 'en_attente')
    $journalistesEnAttente = $this->userRepository->createQueryBuilder('u')
        ->where('u.roles LIKE :role')
        ->andWhere('u.statut_validation = :statut')
        ->setParameter('role', '%ROLE_JOURNALISTE%')
        ->setParameter('statut', 'en_attente')
        ->setMaxResults(5)
        ->getQuery()
        ->getResult();
    
    $signalements = $this->reportRepository->count(['statut' => 'en_attente']);
    
    $factChecksPublies = $this->demandeRepository->count([]);
    
    // Signalements récents avec les relations nécessaires
    $signalementsRecents = $this->reportRepository->createQueryBuilder('r')
        ->leftJoin('r.signalePar', 's')
        ->leftJoin('r.auteurContenu', 'a')
        ->addSelect('s', 'a')
        ->where('r.statut = :statut')
        ->setParameter('statut', 'en_attente')
        ->orderBy('r.dateReport', 'DESC')
        ->setMaxResults(5)
        ->getQuery()
        ->getResult();
    
    // Activité récente simplifiée
    $activiteRecente = [];

    // Derniers utilisateurs sans jointure complexe
    $derniersUtilisateurs = $this->userRepository->findBy(
        ['date_inscription' => null], // Trouver ceux qui ont une date
        ['date_inscription' => 'DESC'],
        3
    );

    // Si pas de date d'inscription, utiliser tous les utilisateurs récents
    if (empty($derniersUtilisateurs)) {
        $derniersUtilisateurs = $this->userRepository->findBy(
            [],
            ['id' => 'DESC'],
            3
        );
    }

    foreach ($derniersUtilisateurs as $user) {
        $activiteRecente[] = [
            'type' => 'user',
            'message' => 'Utilisateur: ' . $user->getPrenom() . ' ' . substr($user->getNom(), 0, 1) . '.',
            'temps' => '1h'
        ];
    }

    // Dernières demandes sans jointure
    $dernieresDemandes = $this->demandeRepository->findBy(
        [],
        ['dateCreation' => 'DESC'],
        3
    );

    foreach ($dernieresDemandes as $demande) {
        // Récupérer l'auteur séparément pour éviter les problèmes de jointure
        $auteur = $demande->getAuteur();
        if ($auteur) {
            $diff = (new \DateTimeImmutable())->diff($demande->getDateCreation());
            $temps = $diff->h > 0 ? $diff->h . 'h' : $diff->i . ' min';
            
            $activiteRecente[] = [
                'type' => 'fact_check',
                'message' => 'Demande par ' . $auteur->getPrenom() . ' ' . substr($auteur->getNom(), 0, 1) . '.',
                'temps' => $temps
            ];
        }
    }

    return $this->render('admin/dashboard.html.twig', [
        'totalUsers' => $totalUsers,
        'journalistesValides' => $journalistesValides,
        'journalistesEnAttente' => $journalistesEnAttente,
        'signalements' => $signalements,
        'factChecksPublies' => $factChecksPublies,
        'signalementsRecents' => $signalementsRecents,
        'activiteRecente' => $activiteRecente
    ]);
}

    #[Route('/journalistes-attente', name: 'admin_journalistes_attente')]
    public function journalistesAttente(): Response
    {
        $journalistes = $this->userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->andWhere('u.statut_validation = :statut')
            ->setParameter('role', '%ROLE_JOURNALISTE%')
            ->setParameter('statut', 'en_attente')
            ->getQuery()
            ->getResult();

        return $this->render('admin/journalistes_attente.html.twig', [
            'journalistes' => $journalistes
        ]);
    }

    #[Route('/journaliste/{id}/valider', name: 'admin_valider_journaliste')]
    public function validerJournaliste(User $user, Request $request): Response
    {
        $user->setStatutValidation('valide');
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Journaliste validé avec succès');
        
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, 'journalistes-attente')) {
            return $this->redirectToRoute('admin_journalistes_attente');
        }
        
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/journaliste/{id}/rejeter', name: 'admin_rejeter_journaliste')]
    public function rejeterJournaliste(User $user, Request $request): Response
    {
        $user->setStatutValidation(null);
        $user->setRoles(['ROLE_USER']);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Journaliste rejeté, converti en contributeur');
        
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, 'journalistes-attente')) {
            return $this->redirectToRoute('admin_journalistes_attente');
        }
        
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/reports', name: 'admin_reports')]
    public function reports(): Response
    {
        $reports = $this->reportRepository->findByStatut('en_attente');

        return $this->render('admin/reports.html.twig', [
            'reports' => $reports
        ]);
    }

    #[Route('/reports/historique', name: 'admin_reports_historique')]
    public function reportsHistorique(): Response
    {
        $reportsTraites = $this->reportRepository->createQueryBuilder('r')
            ->leftJoin('r.signalePar', 's')
            ->leftJoin('r.auteurContenu', 'a')
            ->addSelect('s', 'a')
            ->andWhere('r.statut IN (:statuts)')
            ->setParameter('statuts', ['traite', 'rejete'])
            ->orderBy('r.dateReport', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/reports_historique.html.twig', [
            'reports' => $reportsTraites
        ]);
    }

    #[Route('/reports/{id}', name: 'admin_report_detail', requirements: ['id' => '\d+'])]
    public function reportDetail(Report $report): Response
    {
        if ($report->getTypeContenu() === 'demande') {
            $contenu = $this->demandeRepository->find($report->getIdContenu());
        } else {
            $contenu = $this->reponseRepository->find($report->getIdContenu());
        }

        return $this->render('admin/report_detail.html.twig', [
            'report' => $report,
            'contenu' => $contenu
        ]);
    }

    #[Route('/reports/{id}/action/{action}', name: 'admin_report_action', requirements: ['id' => '\d+'])]
    public function reportAction(Report $report, string $action): Response
    {
        $actions = ['ignorer', 'avertir', 'supprimer_contenu', 'bannir_temporaire', 'bannir_definitivement'];
        
        if (!in_array($action, $actions)) {
            throw $this->createNotFoundException('Action invalide');
        }

        if ($report->getTypeContenu() === 'demande') {
            $contenu = $this->demandeRepository->find($report->getIdContenu());
        } else {
            $contenu = $this->reponseRepository->find($report->getIdContenu());
        }

        $auteur = $report->getAuteurContenu();

        switch ($action) {
            case 'ignorer':
                $report->setStatut('rejete');
                $this->addFlash('success', 'Signalement ignoré');
                break;

            case 'avertir':
                $report->setStatut('traite');
                $this->addFlash('success', 'Utilisateur averti');
                break;

            case 'supprimer_contenu':
                if ($contenu) {
                    $this->entityManager->remove($contenu);
                    $this->addFlash('success', 'Contenu supprimé avec succès');
                }
                $report->setStatut('traite');
                break;

            case 'bannir_temporaire':
                if ($auteur) {
                    $auteur->setStatutModeration('banni_temporaire');
                    $this->addFlash('success', 'Utilisateur banni temporairement');
                }
                $report->setStatut('traite');
                break;

            case 'bannir_definitivement':
                if ($auteur) {
                    $auteur->setStatutModeration('banni');
                    $this->addFlash('success', 'Utilisateur banni définitivement');
                }
                $report->setStatut('traite');
                break;
        }

        $this->entityManager->flush();
        return $this->redirectToRoute('admin_reports');
    }

    #[Route('/reports/traiter/{id}/{action}', name: 'admin_traiter_report', requirements: ['id' => '\d+'])]
    public function traiterReport(Report $report, string $action): Response
    {
        if (!in_array($action, ['approuver', 'rejeter'])) {
            throw $this->createNotFoundException('Action invalide');
        }

        $report->setStatut($action === 'approuver' ? 'traite' : 'rejete');
        $this->entityManager->flush();

        $this->addFlash('success', 'Signalement ' . ($action === 'approuver' ? 'approuvé' : 'rejeté'));
        return $this->redirectToRoute('admin_reports');
    }

    #[Route('/users', name: 'admin_users')]
    public function users(): Response
    {
        $users = $this->userRepository->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/demandes', name: 'admin_demandes')]
    public function demandes(): Response
    {
        $demandes = $this->demandeRepository->createQueryBuilder('d')
            ->leftJoin('d.auteur', 'a')
            ->leftJoin('d.categorie', 'c')
            ->addSelect('a', 'c')
            ->orderBy('d.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $this->render('admin/demandes.html.twig', [
            'demandes' => $demandes
        ]);
    }
}