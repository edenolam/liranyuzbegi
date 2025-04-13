<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController
{
    #[Route('/change-locale', name: 'change_locale')]
    public function changeLocale(Request $request, SessionInterface $session): RedirectResponse
    {
        $locale = $request->query->get('locale', 'he'); // Par défaut : hébreu
        $session->set('_locale', $locale);
        return new RedirectResponse($request->headers->get('referer', '/'));
    }
}


