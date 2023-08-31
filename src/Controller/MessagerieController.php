<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Message;
use App\Form\ReponseType;
use App\Entity\Messagerie;
use App\Entity\Conversation;
use App\Form\MessagerieType;
use Symfony\Component\Mime\Email;
use App\Repository\MessagerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessagerieController extends AbstractController
{
    #[Route('/messagerie', name: 'app_messagerie')]
    public function index(): Response
    {
        $user = $this->getUser();
        if(!$user)
        {
            return $this->redirectToRoute('app_jeux');
        }
        return $this->render('messagerie/index.html.twig', [
            'controller_name' => 'MessagerieController',
        ]);
    }
    // #[Route('/messagerie/nouveau', name: 'nouveau_message')]
    // public function nouveauMessage(MessagerieRepository $repo, EntityManagerInterface $manager, Request $req): Response 
    // {
    //     $message = new Messagerie;
    //     $form = $this->createForm(MessagerieType::class, $message);
    //     $form->handleRequest($req);

    //     if($form->isSubmitted() && $form->isValid())
    //     {
    //         $message->setIsRead(0);
    //         $message->setSender($this->getUser());
    //         $manager->persist($message);
    //         $manager->flush();
    //         $this->addFlash('message', 'Message envoyé avec succès !');
    //         return $this->redirectToRoute('app_messagerie');
    //     }

    //     return $this->render('messagerie/message.html.twig', [
    //         'form' => $form,
    //     ]);
    // }
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
    public function reponseAnnonce(MessagerieRepository $repo, Request $req, EntityManagerInterface $manager, MailerInterface $mailer, Message $message): Response 
    {
        $conversation = new Conversation;
        $reponse = new Messagerie;
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $reponse->setTitle($message->getTitre());
            $reponse->setRecipient($message->getUser());
            $reponse->setSender($this->getUser());
            $reponse->setIsRead(0);
            $conversation->setJeux($message->getJeux());
            $conversation->setChercheur($this->getUser());
            $conversation->setAnnonceur($message->getUser());
            $conversation->setCreatedAt(new \DateTimeImmutable);
            $conversation->setSujet($message->getTitre());
            $conversation->addMessage($reponse);
            $manager->persist($conversation);
            $manager->persist($reponse);
            $manager->flush();
            $manager->flush();
            $email = (new Email())
            ->from('hello@example.com')
            ->to($message->getUser()->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Nouvelle réponse à votre annonce')
            ->text('Vous avez recu une nouvelle réponse à votre annonce, allez la consulter.')
            ->html('<p>See Twig integration for better HTML integration!</p>');
            $mailer->send($email);

            return $this->redirectToRoute('liste_jeux');
        }

        return $this->render('messagerie/reponseAnnonce.html.twig', [
            'form' => $form
        ]);
    }
    #[Route('/message/contact/{id}', name: 'contact_user')]
    public function messageToUser(MessagerieRepository $repo, Request $req, EntityManagerInterface $manager, MailerInterface $mailer, User $user)
    {
        $conversation = new Conversation;
        $message = new Messagerie;
        $form = $this->createForm(MessagerieType::class, $message);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $message->setRecipient($user);
            $message->setSender($this->getUser());
            $message->setIsRead(0);
            $conversation->setChercheur($user);
            $conversation->setAnnonceur($this->getUser());
            $conversation->setCreatedAt(new \DateTimeImmutable);
            $conversation->setSujet($message->getTitle());
            $conversation->addMessage($message);
            $manager->persist($conversation);
            $manager->persist($message);
            $manager->flush();
            $manager->flush();
            $email = (new Email())
            ->from('hello@example.com')
            ->to($user->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($message->getTitle())
            ->text($this->getUser()->getPseudo() . 'vous a contacté, répondez-lui.')
            ->html('<p>Vous avez un nouveau message dans votre boîte de réception.</p>');
            $mailer->send($email);

            return $this->redirectToRoute('visite_profil', ['id' => $user->getId()]);


        }

        return $this->render('/messagerie/contact.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }
    #[Route('/messagerie/conversations/{id}', name: 'app_conversation')]
    public function conversation(ConversationRepository $repo, User $user)
    {
        $convSend = $repo->findBy(['annonceur' => $user], ['created_at' => 'DESC']);
        $convReceived = $repo->findBy(['chercheur' => $user], ['created_at' => 'DESC']);

        return $this->render('messagerie/conversation.html.twig', [
            'convSend' => $convSend,
            'convReceived' => $convReceived,
        ]);
    }
    #[Route('/messagerie/show/conversation/{id}', name: 'show_conversation')]
    #[Route('/messagerie/reponseConversation/{id}', name: 'reponse_conversation')]
    public function showConversation(ConversationRepository $repo, EntityManagerInterface $manager, Request $req, MailerInterface $mailer, Conversation $conversation)
    {
        $conv = $repo->findOneBy(['id' => $conversation->getId()]);
        $reponse = new Messagerie;
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $conv->addMessage($reponse);
            if($this->getUser() == $conv->getAnnonceur())
            {
                $reponse->setRecipient($conv->getChercheur());
                $target = $conv->getChercheur();

            } else 
            {
                $reponse->setRecipient($conv->getAnnonceur());
                $target = $conv->getAnnonceur();
            }
            $reponse->setSender($this->getUser());
            $reponse->setIsRead(0);
            $reponse->setTitle($conv->getSujet());
            $manager->persist($conv);
            $manager->persist($reponse);
            $manager->flush();
            $manager->flush();

            $email = (new Email())
            ->from('hello@example.com')
            ->to($target->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($conv->getSujet())
            ->text($this->getUser()->getPseudo() . ' vous a répondu, allez consulter sa réponse.')
            ->html('<p>Vous avez un nouveau message dans votre boîte de réception.</p>');
            $mailer->send($email);

            return $this->redirectToRoute('show_conversation', ['id' => $conv->getId()]);
        }



        return $this->render('messagerie/showConversation.html.twig', [
            'form' => $form,
            'conv' => $conv,
        ]);
    }
}
