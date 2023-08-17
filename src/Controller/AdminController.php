<?php

namespace App\Controller;

use App\Entity\Jeux;
use App\Entity\User;
use App\Form\AdminJeuxType;
use App\Form\AdminUserType;
use App\Form\AdminUserModifType;
use App\Repository\JeuxRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    #[Route('/admin/users', name: 'admin_user')]
    #[Route('admin/createUser', name: 'create_user')]
    public function adminUser(UserRepository $repo, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher, User $user = null): Response 
    {
        $users = $repo->findAll();
        $user = new User;
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $roles = [];
            $pwd = $form->get('password')->getData();
            $hashedPwd = $passwordHasher->hashPassword($user, $pwd);
            $user->setPassword($hashedPwd);
            $user->setDateInscription(new \DateTimeImmutable);
            if($form->get('roles')->getData() == 'Administrateur')
            {
                $roles = ["ROLE_ADMIN"];
            } else 
            {
                $roles = ["ROLE_USER"];
            }
            $user->setRoles($roles);
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('admin_user');
        }

        return $this->render('admin/adminUser.html.twig', [
            'users' => $users,
            'form' => $form,
        ]);
    }
    #[Route('admin/deleteUser/{id}', name: 'delete_user')]
    public function deleteUser(EntityManagerInterface $manager, User $user)
    {
        $manager->remove($user);
        $manager->flush();

        return $this->redirectToRoute('admin_user');
    }
    #[Route('admin/update_user/{id}', name: 'update_user')]
    public function updateUser(EntityManagerInterface $manager, Request $req, User $user)
    {
        $form = $this->createForm(AdminUserModifType::class, $user);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            if($form->get('roles')->getData() == 'Administrateur')
            {
                $roles = ["ROLE_ADMIN"];
            } else 
            {
                $roles = ["ROLE_USER"];
            }
            $user->setRoles($roles);
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('admin_user');
        }

        return $this->render('admin/adminUpdateUser.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
    #[Route('admin/jeux', name: 'admin_jeux')]
    #[Route('admin/jeux/new', name: 'new_jeux')]
    public function adminJeux(JeuxRepository $repo, Request $req, EntityManagerInterface $manager, SluggerInterface $slugger)
    {
        $jeux = $repo->findAll();
        $game = new Jeux;
        $form = $this->createForm(AdminJeuxType::class, $game);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $brochureFile = $form->get('photo')->getData();

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
            $game->setPhoto($newFilename);
            }
            $manager->persist($game);
            $manager->flush();

            return $this->redirectToRoute('admin_jeux');
        }
        return $this->render('admin/adminJeux.html.twig', [
            'jeux' => $jeux,
            'form' => $form,
        ]);

    }    
}
