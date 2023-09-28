<?php

namespace App\Controller;

use LogicException;
use App\Entity\Jeux;
use App\Entity\User;
use App\Form\UserType;
use App\Entity\Message;
use App\Form\MessageType;
use App\Form\UpdateProfilType;
use App\Form\UpdateMessageType;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/inscription', name: 'inscription')]
    public function index(EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher, Request $req, MailerInterface $mailer): Response
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
            $user->setPhotoProfil('manette.jpg');
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
    #[Route('/profil/{id}', name: 'profil_perso')]
    public function profilPerso(UserRepository $repo, User $user)
    {
        $user = $this->getUser();
        $jeux = $this->getUser()->getJeux();
        $plateformes = $this->getUser()->getPlateformes();
        return $this->render('user/profil.html.twig', [
            'jeux' => $jeux,
            'plateformes' => $plateformes,
            'user' => $user,
        ]);
    }
    #[Route('/deleteJeuxFromProfile/{id}', name: 'delete_jeux_profile')]
    public function deleteJeuxFromProfile(User $user, EntityManagerInterface $manager, Jeux $jeux)
    {
        $user = $this->getUser();
        $modif = $user->removeJeux($jeux);
        $manager->persist($modif);
        $manager->flush();

        return $this->redirectToRoute('profil_perso', ['id' => $user->getId()]);
    }
    #[Route('profil/update/{id}', name: 'modif_profil')]
    public function modifProfil(EntityManagerInterface $manager, Request $req, SluggerInterface $slugger, User $user)
    {
        $choices = [
            'Fleur' => '<img src="../public/images/fleur.jpg" alt="Une fleur">',
            'Manette' => '<img src="./public/images/manette.jpg" alt="Une manette">',
            'Poisson' => '<img src="./public/images/poisson.jpg" alt="Un poisson">',
            'Game Over' => '<img src="./public/images/gameover.jpg" alt="Game Over">',
            'Lune' => '<img src="./public/images/lune.jpg" alt="Une lune">',
        ];
        $form = $this->createForm(UpdateProfilType::class, $user, ['choices' => $choices]);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            
            $brochureFile = $form->get('photo_profil')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) 
            {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $user->setPhotoProfil($newFilename);


                // Pour les photos suggérés :
                // $brochureFile = $form->get('photos')->getData();

                // // this condition is needed because the 'brochure' field is not required
                // // so the PDF file must be processed only when a file is uploaded
                // if ($brochureFile) 
                // {
                //     $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                //     // this is needed to safely include the file name as part of the URL
                //     $safeFilename = $slugger->slug($originalFilename);
                //     $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();
    
                //     // Move the file to the directory where brochures are stored
                //     try {
                //         $brochureFile->move(
                //             $this->getParameter('photos_directory'),
                //             $newFilename
                //         );
                //     } catch (FileException $e) {
                //         // ... handle exception if something happens during file upload
                //     }
    
                //     // updates the 'brochureFilename' property to store the PDF file name
                //     // instead of its contents
                //     $user->setPhotoProfil($newFilename);
                // }
            }
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('profil_perso', ['id' => $user->getId()]);
        }

        return $this->render('user/modifProfil.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/visiteProfil/{id}', name: 'visite_profil')]
    public function visiteProfil(User $user): Response
    {


        return $this->render('user/visiteProfil.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/mesAnnonces/{id}', name: 'mes_annonces')]
    public function mesAnnonces()
    {


        return $this->render('user/mesAnnonces.html.twig', [

        ]);
    }
    #[Route('/mesAnnonces/edit/{id}', name: 'edit_annonce')]
    public function editAnnonce(EntityManagerInterface $manager, MessageRepository $repo, Request $req, Message $message)
    {

        $form = $this->createForm(UpdateMessageType::class, $message);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($message);
            $manager->flush();

            return $this->redirectToRoute('mes_annonces', ['id' => $this->getUser()->getId()]);
        }

        return $this->render('/user/editAnnonce.html.twig', [
            'form' => $form,
            'message' => $message
        ]);
    }
    #[Route('/mesAnnonces/delete/{id}', name: 'delete_annonce')]
    public function deleteAnnonce(EntityManagerInterface $manager, Message $message)
    {
        $manager->remove($message);
        $manager->flush();

        return $this->redirectToRoute('mes_annonces', ['id' => $this->getUser()->getId()]);
    }
}
