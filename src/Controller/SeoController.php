<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SeoController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'seo_sitemap', methods: ['GET'])]
    public function sitemap(): Response
    {
        $response = $this->render('seo/sitemap.xml.twig');
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
    }

    #[Route('/robots.txt', name: 'seo_robots', methods: ['GET'])]
    public function robots(): Response
    {
        $sitemapUrl = $this->generateUrl('seo_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $content = <<<TEXT
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /login

Sitemap: $sitemapUrl
TEXT;

        return new Response($content . "\n", Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
