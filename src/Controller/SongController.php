<?php

namespace App\Controller;

use App\Entity\Song;
use App\Repository\SongRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SongController extends AbstractController
{
    #[Route('/songs', name: 'app_song_list', methods: ['GET'])]
    public function list(SongRepository $songRepository): Response
    {
        return $this->render('song/list.html.twig', [
            'songs' => $songRepository->findAll()
        ]);
    }

    #[Route('/song/{id}', name: 'app_song_show', requirements: ['id' => '\d+'])]
    public function show(Song $song): Response
    {
        return $this->render('song/show.html.twig', [
            'song' => $song,
        ]);
    }
}
