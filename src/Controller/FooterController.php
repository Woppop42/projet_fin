<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FooterController extends AbstractController
{
    #[Route('/mentionslegales', name: 'mentions_legales')]
    public function index(): Response
    {
        return $this->render('footer/index.html.twig', [
            'controller_name' => 'FooterController',
        ]);
    }

    #[Route('/cgu', name: 'cgu')]
    public function cgu()
    {
        return $this->render('footer/cgu.html.twig');
    }
}
