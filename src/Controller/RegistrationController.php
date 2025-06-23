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
                $user->setStatutModeration('actif');
                // Récupérer les données spécifiques aux journalistes depuis la requête
                $numeroCartePresse = $request->request->get('numeroCartePresse');
               
                if ($numeroCartePresse) {
                    $user->setNumeroCartePresse((int) $numeroCartePresse);
                }
            } else {
                $user->setRoles(['ROLE_USER']);
                $user->setStatutValidation(NULL);
                $user->setStatutModeration('actif');
            }
           
            // Définir la date d'inscription
            $user->setDateInscription(new \DateTimeImmutable());
           
            // Initialiser le score de réputation
            $user->setScoreReputation(0.0);
           
            // Définir le statut de modération
            $user->setStatutModeration('actif');
            
            $entityManager->persist($user);
            $entityManager->flush();

            if ($userType === 'journaliste') {
                // Ajouter le message dans la session pour l'alert
                $this->addFlash('journalist_pending', 'Votre demande d\'accréditation journaliste a été soumise. Votre compte sera activé après validation par notre équipe.');
                // Redirection vers la homepage
                return $this->redirectToRoute('app_home'); // ou le nom de votre route homepage
            } else {
                $this->addFlash('success', 'Votre compte a été créé avec succès !');
                // Connexion automatique pour les contributeurs
                return $security->login($user, 'form_login', 'main');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}