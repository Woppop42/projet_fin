<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\ReponseType;
use App\Entity\Messagerie;
use App\Form\MessagerieType;
use App\Repository\MessagerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessagerieController extends AbstractController
{
    #[Route('/messagerie', name: 'app_messagerie')]
    public function index(): Response
    {
        return $this->render('messagerie/index.html.twig', [
            'controller_name' => 'MessagerieController',
        ]);
    }
    #[Route('/messagerie/nouveau', name: 'nouveau_message')]
    public function nouveauMessage(MessagerieRepository $repo, EntityManagerInterface $manager, Request $req): Response 
    {
        $message = new Messagerie;
        $form = $this->createForm(MessagerieType::class, $message);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $message->setIsRead(0);
            $message->setSender($this->getUser());
            $manager->persist($message);
            $manager->flush();
            $this->addFlash('message', 'Message envoyé avec succès !');
            return $this->redirectToRoute('app_messagerie');
        }

        return $this->render('messagerie/message.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/message/received', name: 'received_message')]
    public function messageRecu(): Response 
    {
        return $this->render('messagerie/recu.html.twig');
    }
    #[Route('/message/lecture/{id}', name: 'lire_message')]
    public function lireMessage(MessagerieRepository $repo, $id): Response
    {
        $message = $repo->find($id);
        $message->setIsRead(true);

        return $this->render('messagerie/lire.html.twig', [
            'message' => $message,
        ]);
    }
    #[Route('/message/delete/{id}', name: 'delete_message')]
    public function deleteMessage(EntityManagerInterface $manager, Messagerie $message)
    {
        $manager->remove($message);
        $manager->flush();
        $this->addFlash('messageSupp', 'Message correctement supprimé.');
        
        return $this->redirectToRoute('received_message');
    }
    #[Route('message/sent', name: 'sent_message')]
    public function messageEnvoyes(): Response
    {
        return $this->render('messagerie/sent.html.twig');
    }
    #[Route('/message/reponse/{id}', name: 'reponse_annonce')]
    public function reponseAnnonce(MessagerieRepository $repo, Request $req, EntityManagerInterface $manager, Message $message): Response 
    {
        $reponse = new Messagerie;
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $reponse->setTitle($message->getTitre());
            $reponse->setRecipient($message->getUser());
            $reponse->setSender($this->getUser());
            $reponse->setIsRead(0);
            $manager->persist($reponse);
            $manager->flush();
            return $this->redirectToRoute('liste_jeux');
        }

        return $this->render('messagerie/reponseAnnonce.html.twig', [
            'form' => $form
        ]);
    }
}
