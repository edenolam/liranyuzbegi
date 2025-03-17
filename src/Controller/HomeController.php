<?php

namespace App\Controller;

use App\Repository\SongRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(SongRepository $songRepository): Response
    {
        $latestSongs = $songRepository->findLatestSongs(6);

        return $this->render('homepage.html.twig', [
            'latestSongs' => $latestSongs,
        ]);
    }
}

