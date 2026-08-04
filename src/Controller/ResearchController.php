<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ResearchController extends AbstractController
{
    #[Route('/research-and-artistic-practice', name: 'research')]
    #[Route('/en/research-and-artistic-practice', name: 'localized_research')]
    public function index(): Response
    {
        return $this->render('research/index.html.twig');
    }
}
