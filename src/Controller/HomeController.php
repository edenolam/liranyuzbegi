<?php

namespace App\Controller;

use App\Entity\WorkshopAnnouncement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('homepage.html.twig', [
            'announcement' => $entityManager->find(WorkshopAnnouncement::class, WorkshopAnnouncement::HOMEPAGE_ID)
                ?? new WorkshopAnnouncement(),
        ]);
    }
}
