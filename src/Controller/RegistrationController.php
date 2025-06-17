<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            
            // Récupérer le type d'utilisateur sélectionné
            $userType = $form->get('userType')->getData();
            
            // Encoder le mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            
            // Définir les rôles selon le type d'utilisateur
            if ($userType === 'journaliste') {
                $user->setRoles(['ROLE_JOURNALISTE']);
                $user->setStatutValidation('en_attente'); // En attente de validation
                
                // Récupérer les données spécifiques aux journalistes depuis la requête
                $numeroCartePresse = $request->request->get('numeroCartePresse');
                
                if ($numeroCartePresse) {
                    $user->setNumeroCartePresse((int) $numeroCartePresse);
                }
                
                
            } else {
                $user->setRoles(['ROLE_CONTRIBUTEUR']);
                $user->setStatutValidation('actif'); // Actif immédiatement
            }
            
            // Définir la date d'inscription
            $user->setDateInscription(new \DateTimeImmutable());
            
            // Initialiser le score de réputation
            $user->setScoreReputation(0.0);
            
            // Définir le statut de modération
            $user->setStatutModeration('actif');

            $entityManager->persist($user);
            $entityManager->flush();

            // Ajouter un message flash selon le type
            if ($userType === 'journaliste') {
                $this->addFlash('info', 'Votre demande d\'accréditation journaliste a été soumise. Votre compte sera activé après validation.');
            } else {
                $this->addFlash('success', 'Votre compte a été créé avec succès !');
            }

            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}