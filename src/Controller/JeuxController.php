<?php

namespace App\Controller;

use App\Entity\Jeux;
use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\JeuxRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class JeuxController extends AbstractController
{
    #[Route('/', name: 'app_jeux')]
    public function index(): Response
    {
        return $this->render('jeux/index.html.twig', [
            
        ]);
    }
    #[Route('/jeux', name: 'liste_jeux')]
    public function jeux(JeuxRepository $repo)
    {
        $jeux = $repo->findAll();

        return $this->render('jeux/liste.html.twig', [
            'jeux' => $jeux,
        ]);
    }
    #[Route('/jeux/fiche/{id}', name: 'fiche_jeux')]
    #[Route('/message/nouveau', name: 'nouveau_message')]
    public function unJeux(JeuxRepository $repo, MessageRepository $mrepo, EntityManagerInterface $manager, Jeux $jeux, Request $req, $id): Response
    {
        $jeux = $repo->find($id);
        $messages = $mrepo->findBy(["jeux" => $jeux]);
        $message = new Message;
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $message->setUser($this->getUser());
            $message->setType('Message de recherche');
            $message->setJeux($jeux);
            $message->setDateMessage(new \DateTimeImmutable);
            $manager->persist($message);
            $manager->flush();

            $this->addFlash('newMessage', 'Votre annonce a bien été postée.');
            return $this->redirectToRoute('fiche_jeux', ['id' => $id]);
        }
        
        return $this->render('jeux/fiche.html.twig', [
            'form' => $form,
            'jeux' => $jeux,
            'messages' => $messages
        ]);

    }
    #[Route('/jeux/ajout/{id}', name: 'ajout_jeux')]
    public function ajoutJeux(EntityManagerInterface $manager, Jeux $jeux)
    {
        $user = $this->getUser();
        $user->addJeux($jeux);
        $manager->persist($user);
        $manager->flush();
        return $this->redirectToRoute('liste_jeux');
    }
}
