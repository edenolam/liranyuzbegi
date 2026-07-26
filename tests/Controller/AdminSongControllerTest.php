<?php

namespace App\Tests\Controller;

use App\Entity\Song;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminSongControllerTest extends WebTestCase
{
    public function testAdminCanLogInWithCsrfProtectedForm(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $admin = (new User())
            ->setEmail(uniqid('login-test-', true) . '@example.com')
            ->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($passwordHasher->hashPassword($admin, 'test-password'));
        $entityManager->persist($admin);
        $entityManager->flush();

        $client->request('GET', '/change-locale?locale=en');
        $crawler = $client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="_csrf_token"]');
        $form = $crawler->selectButton('Log in')->form([
            '_username' => $admin->getEmail(),
            '_password' => 'test-password',
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/admin/song');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Manage Songs');

        $managedAdmin = $entityManager->find(User::class, $admin->getId());
        if ($managedAdmin !== null) {
            $entityManager->remove($managedAdmin);
            $entityManager->flush();
        }
    }

    public function testAdminCanCompleteSongCrud(): void
    {
        $client = static::createClient();
        $admin = $this->authenticateAdmin($client);
        $client->request('GET', '/change-locale?locale=en');

        $crawler = $client->request('GET', '/admin/song/new');
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('Save')->form([
            'song[title]' => 'Admin CRUD test',
            'song[artist]' => 'Test artist',
            'song[year]' => '2026',
            'song[album]' => 'Test album',
        ]);
        $form['song[coverImage]']->upload(
            dirname(__DIR__, 2) . '/public/uploads/covers/defaultcoverimage.png'
        );
        $client->submit($form);
        self::assertResponseStatusCodeSame(303);

        $song = $this->findSongByTitle('Admin CRUD test');
        self::assertNotNull($song);
        self::assertNotNull($song->getCoverImage());
        $uploadedCoverPath = dirname(__DIR__, 2) . '/public/uploads/covers/' . $song->getCoverImage();
        self::assertFileExists($uploadedCoverPath);

        $client->request('GET', '/admin/song/' . $song->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table', 'Admin CRUD test');

        $crawler = $client->request('GET', '/admin/song/' . $song->getId() . '/edit');
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('Update')->form([
            'song[title]' => 'Admin CRUD test updated',
            'song[artist]' => 'Updated artist',
            'song[year]' => '2025',
            'song[album]' => '',
        ]);
        $client->submit($form);
        self::assertResponseStatusCodeSame(303);

        $song = $this->findSongByTitle('Admin CRUD test updated');
        self::assertNotNull($song);
        self::assertSame('Updated artist', $song->getArtist());
        self::assertNull($song->getAlbum());

        $crawler = $client->request('GET', '/admin/song/' . $song->getId());
        $deleteForm = $crawler->selectButton('Delete')->form();
        $client->submit($deleteForm);
        self::assertResponseStatusCodeSame(303);
        self::assertNull($this->findSongByTitle('Admin CRUD test updated'));
        self::assertFileDoesNotExist($uploadedCoverPath);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $managedAdmin = $entityManager->find(User::class, $admin->getId());
        if ($managedAdmin !== null) {
            $entityManager->remove($managedAdmin);
            $entityManager->flush();
        }
    }

    private function authenticateAdmin(KernelBrowser $client): User
    {
        $admin = (new User())
            ->setEmail(uniqid('admin-test-', true) . '@example.com')
            ->setPassword('unused')
            ->setRoles(['ROLE_ADMIN']);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($admin);
        $entityManager->flush();
        $client->loginUser($admin);

        return $admin;
    }

    private function findSongByTitle(string $title): ?Song
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        return $entityManager->getRepository(Song::class)->findOneBy(['title' => $title]);
    }
}
