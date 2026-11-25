<?php

namespace App\Controller\Admin;

use App\Entity\WorkshopAnnouncement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WorkshopAnnouncementController extends AbstractController
{
    #[Route('/admin/workshop-announcement', name: 'app_admin_workshop_announcement', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $announcement = $entityManager->find(WorkshopAnnouncement::class, WorkshopAnnouncement::HOMEPAGE_ID)
            ?? new WorkshopAnnouncement();

        $form = $this->createFormBuilder($announcement)
            ->add('title', TextType::class, ['label' => 'Event title'])
            ->add('venue', TextType::class, ['label' => 'Institution or venue'])
            ->add('period', TextType::class, ['label' => 'Date or period'])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($announcement);
            $entityManager->flush();

            $this->addFlash('success', 'The homepage announcement has been updated.');

            return $this->redirectToRoute('app_admin_workshop_announcement', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/workshop_announcement/edit.html.twig', ['form' => $form]);
    }
}
