<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class LegacyLocaleRedirectController extends AbstractController
{
    #[Route('/{_locale}', name: 'localized_home', requirements: ['_locale' => 'en|he'], priority: -10)]
    public function home(): RedirectResponse
    {
        return $this->redirectToRoute('home', status: RedirectResponse::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/{_locale}/about', name: 'localized_about', requirements: ['_locale' => 'en|he'], priority: -10)]
    public function about(): RedirectResponse
    {
        return $this->redirectToRoute('about', status: RedirectResponse::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/{_locale}/contact', name: 'localized_contact', requirements: ['_locale' => 'en|he'], priority: -10)]
    public function contact(): RedirectResponse
    {
        return $this->redirectToRoute('contact', status: RedirectResponse::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/{_locale}/research-and-artistic-practice', name: 'localized_research', requirements: ['_locale' => 'en|he'], priority: -10)]
    public function research(): RedirectResponse
    {
        return $this->redirectToRoute('research', status: RedirectResponse::HTTP_MOVED_PERMANENTLY);
    }
}
