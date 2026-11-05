<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class StructuredIdentityTest extends WebTestCase
{
    /** @dataProvider englishPages */
    public function testIdentityMarkupKeepsTheVisiblePageInEnglish(string $path, string $title): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('html[lang="en"][dir="ltr"]');
        self::assertSame($title, $crawler->filter('title')->text());
        self::assertDoesNotMatchRegularExpression('/[\x{0590}-\x{05FF}]/u', $crawler->filter('body')->text());

        $scripts = $crawler->filter('script[type="application/ld+json"]');
        self::assertCount(1, $scripts);
        $data = json_decode($scripts->text(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('https://schema.org', $data['@context']);

        $nodes = array_column($data['@graph'], null, '@type');
        $person = $nodes['Person'];
        self::assertSame('Liran Yuzbegi', $person['name']);
        self::assertSame('לירן יוזבגי', $person['alternateName']);
        self::assertSame('https://liranyuzbegi.com/#liran-yuzbegi', $person['@id']);
        self::assertSame([
            'https://online.uni-graz.at/kfu_online/pl/ui/%24ctx%3Bdesign%3Dca2%3Bheader%3Dmax/visitenkarte.show_vcard?pPersonenGruppe=3&pPersonenId=7C2DB5B86E0AA36F',
            'https://open.spotify.com/artist/515MChdibJUMXGOKs0laMv',
        ], $person['sameAs']);

        $page = $nodes[$path === '/about' ? 'ProfilePage' : 'WebPage'];
        $canonical = $crawler->filter('link[rel="canonical"]')->attr('href');
        self::assertSame($canonical, $page['url']);
        self::assertSame($canonical.'#webpage', $page['@id']);
        self::assertSame('en', $page['inLanguage']);
        self::assertSame($person['@id'], $page['about']['@id']);
        self::assertSame($nodes['WebSite']['@id'], $page['isPartOf']['@id']);
        self::assertSame($person['@id'], $nodes['WebSite']['publisher']['@id']);
        if ($path === '/about') {
            self::assertSame($person['@id'], $page['mainEntity']['@id']);
            self::assertSame($page['@id'], $person['mainEntityOfPage']['@id']);
        }
    }

    public static function englishPages(): iterable
    {
        yield 'home' => ['/', 'Liran Yuzbegi | Researcher, Composer & Experimental Filmmaker'];
        yield 'about' => ['/about', 'About | Liran Yuzbegi'];
        yield 'research' => ['/research-and-artistic-practice', 'Research and Artistic Practice | Liran Yuzbegi'];
        yield 'contact' => ['/contact', 'Contact | Liran Yuzbegi'];
    }
}
