<?php

namespace App\Controller\Admin;

use App\Entity\Song;
use App\Form\SongType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/song')]
final class SongController extends AbstractController
{
    #[Route(name: 'app_admin_song_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $songs = $entityManager
            ->getRepository(Song::class)
            ->findAll();

        return $this->render('admin/song/index.html.twig', [
            'songs' => $songs,
        ]);
    }

    #[Route('/new', name: 'app_admin_song_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $song = new Song();
        $form = $this->createForm(SongType::class, $song);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFileUpload($song, $form);
            $entityManager->persist($song);
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_song_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/song/new.html.twig', [
            'song' => $song,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_song_show', methods: ['GET'])]
    public function show(Song $song): Response
    {
        return $this->render('admin/song/show.html.twig', [
            'song' => $song,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_song_edit', methods: ['GET', 'POST'])]
    public function edit(Song $song, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SongType::class, $song);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFileUpload($song, $form);
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_song_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('admin/song/edit.html.twig', [
            'song' => $song,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_song_delete', methods: ['POST'])]
    public function delete(Request $request, Song $song, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $song->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($song);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_song_index', [], Response::HTTP_SEE_OTHER);
    }

    private function handleFileUpload(Song $song, FormInterface $form): void
    {
        $coverFile = $form->get('coverImage')->getData();
        if ($coverFile) {
            $newFilename = $this->uploadFile($coverFile, 'covers');
            $song->setCoverImage($newFilename);
        }

        $audioFile = $form->get('audioFile')->getData();
        if ($audioFile) {
            $newFilename = $this->uploadFile($audioFile, 'songs');
            $song->setAudioFile($newFilename);
        }
    }

    private function uploadFile(UploadedFile $file, string $folder): string
    {
        $destination = $this->getParameter('kernel.project_dir') . "/public/uploads/$folder";
        $newFilename = uniqid() . '.' . $file->guessExtension();
        $file->move($destination, $newFilename);

        return $newFilename;
    }

}
