<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationSmokeTest extends WebTestCase
{
    /**
     * @dataProvider publicPageProvider
     */
    public function testPublicPagesAreAvailable(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
    }

    public function testAdminRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/song');

        self::assertResponseRedirects('/login');
    }

    public function testEnglishAboutPageKeepsOnlyHeadingInHebrew(): void
    {
        $client = static::createClient();
        $client->request('GET', '/change-locale?locale=en');
        $client->request('GET', '/about');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'לירן יוזבגי');
        self::assertSelectorTextContains('.about-profile__intro', 'Liran Yuzbegi is an interdisciplinary artist');
        self::assertSelectorTextNotContains('.about-profile__intro', 'לירן יוזבגי');
    }

    public function testContactFormCanBeSubmitted(): void
    {
        $client = static::createClient();
        $client->request('GET', '/change-locale?locale=en');
        $crawler = $client->request('GET', '/contact');
        $form = $crawler->selectButton('Send')->form([
            'contact[name]' => 'Functional test',
            'contact[email]' => 'test@example.com',
            'contact[message]' => 'Automated contact form verification.',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/contact');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Message sent');
    }

    public function testLocalizedPagesExposeBilingualSeoMetadata(): void
    {
        $client = static::createClient();

        $client->request('GET', '/he/about');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('html[lang="he"]');
        self::assertSelectorExists('link[rel="canonical"][href$="/he/about"]');
        self::assertSelectorExists('link[rel="alternate"][hreflang="en"][href$="/en/about"]');
        self::assertSelectorExists('meta[name="description"][content*="לירן יוזבגי"]');

        $client->request('GET', '/en/about');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('html[lang="en"]');
        self::assertSelectorExists('link[rel="canonical"][href$="/en/about"]');
        self::assertSelectorExists('link[rel="alternate"][hreflang="he"][href$="/he/about"]');
        self::assertSelectorExists('meta[name="description"][content*="Liran Yuzbegi"]');
        self::assertSelectorExists('script[type="application/ld+json"]');
    }

    public function testSearchEngineDiscoveryFilesAreAvailable(): void
    {
        $client = static::createClient();

        $client->request('GET', '/sitemap.xml');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/xml; charset=UTF-8');
        self::assertStringContainsString('/he/about', $client->getResponse()->getContent());
        self::assertStringContainsString('/en/about', $client->getResponse()->getContent());

        $client->request('GET', '/robots.txt');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Sitemap:', $client->getResponse()->getContent());
        self::assertStringContainsString('Disallow: /admin/', $client->getResponse()->getContent());
    }

    public function testPublicResponsesIncludeSecurityHeaders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/about');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('X-Content-Type-Options', 'nosniff');
        self::assertResponseHeaderSame('X-Frame-Options', 'DENY');
        self::assertResponseHeaderSame('Referrer-Policy', 'strict-origin-when-cross-origin');
        self::assertResponseHeaderSame('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function publicPageProvider(): iterable
    {
        yield 'homepage' => ['/'];
        yield 'about' => ['/about'];
        yield 'songs' => ['/songs'];
        yield 'contact' => ['/contact'];
        yield 'login' => ['/login'];
    }
}
