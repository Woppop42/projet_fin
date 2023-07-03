<?php

namespace App\Controller;

use LogicException;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/inscription', name: 'inscription')]
    public function index(EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher, Request $req): Response
    {
        $user = new User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid())
        {
            $roles[] = "ROLE_ADMIN";
            $password = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            $user->setDateInscription(new \DateTimeImmutable);
            $user->setRoles($roles);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Félicitations, vous êtes bien inscrit ! ');
            return $this->redirectToRoute('app_jeux');
        }
        return $this->render('user/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $lastUsername = $utils->getLastUsername();
        $this->addFlash('success', "Vous êtes correctement connecté !");
        return $this->render('security/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }
    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        $this->addFlash('deco', 'Vous êtes correctement déconnecté !');
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
