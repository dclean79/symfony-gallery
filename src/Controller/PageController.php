<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('page/about.html.twig', [
            'page_title' => 'About',
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact() : Response {
        return $this->render('page/contact.html.twig', [
            'page_title' => 'Contact'
        ]);
    }
}