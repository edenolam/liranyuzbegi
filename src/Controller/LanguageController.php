<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class LanguageController
{
    #[Route('/change-locale', name: 'change_locale', methods: ['GET'])]
    public function changeLocale(Request $request, SessionInterface $session): RedirectResponse
    {
        $locale = $request->query->getString('locale');
        if (!in_array($locale, ['en', 'he'], true)) {
            $locale = 'he';
        }

        $session->set('_locale', $locale);

        $referer = $request->headers->get('referer');
        $target = '/';
        if ($referer !== null) {
            $refererParts = parse_url($referer);
            $refererHost = $refererParts['host'] ?? null;
            if ($refererHost === null || $refererHost === $request->getHost()) {
                $target = ($refererParts['path'] ?? '/') .
                    (isset($refererParts['query']) ? '?' . $refererParts['query'] : '');
            }
        }

        return new RedirectResponse($target);
    }
}

