<?php
// src/Controller/ReportController.php

namespace App\Controller;

use App\Entity\Report;
use App\Form\ReportType;
use App\Repository\DemandeRepository;
use App\Repository\ReponseRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/report')]
class ReportController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DemandeRepository $demandeRepository,
        private ReponseRepository $reponseRepository,
        private ReportRepository $reportRepository
    ) {}

    #[Route('/signaler/{type}/{id}', name: 'report_contenu', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function signaler(Request $request, string $type, int $id): Response
    {
        if (!in_array($type, ['demande', 'reponse'])) {
            throw $this->createNotFoundException('Type de contenu invalide');
        }

        // Récupérer le contenu
        if ($type === 'demande') {
            $contenu = $this->demandeRepository->find($id);
        } else {
            $contenu = $this->reponseRepository->find($id);
        }

        if (!$contenu) {
            throw $this->createNotFoundException('Contenu non trouvé');
        }

        $currentUser = $this->getUser();

        // Vérifier que l'utilisateur ne signale pas son propre contenu
        if ($contenu->getAuteur() === $currentUser) {
            $this->addFlash('error', 'Vous ne pouvez pas signaler votre propre contenu');
            return $this->redirectToRoute('app_home');
        }

        // Vérifier si déjà signalé
        $reportExistant = $this->reportRepository->findExistingReport($type, $id, $currentUser);
        if ($reportExistant) {
            $this->addFlash('warning', 'Vous avez déjà signalé ce contenu');
            return $this->redirectToRoute('app_home');
        }

        $report = new Report();
        $report->setTypeContenu($type)
            ->setIdContenu($id)
            ->setSignalePar($currentUser)
            ->setAuteurContenu($contenu->getAuteur());

        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($report);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre signalement a été envoyé. Merci de nous aider à améliorer la qualité du contenu.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('report/form.html.twig', [
            'form' => $form,
            'type' => $type,
            'contenu' => $contenu
        ]);
    }
}