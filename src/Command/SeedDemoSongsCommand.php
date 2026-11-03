<?php

namespace App\Command;

use App\Entity\Song;
use App\Repository\SongRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:seed-demo-songs',
    description: 'Adds a small idempotent set of demo songs to the database.',
)]
final class SeedDemoSongsCommand extends Command
{
    /**
     * Replace the titles and metadata below with the definitive information
     * supplied by Liran before publishing the Music page.
     *
     * @var list<array{
     *     title: string,
     *     artist: string,
     *     album: string,
     *     year: int,
     *     coverImage: string,
     *     audioFile: string
     * }>
     */
    private const SONGS = [
        [
            'title' => 'Demo Work 01',
            'artist' => 'Liran Yuzbegi',
            'album' => 'Selected Works',
            'year' => 2024,
            'coverImage' => '67d15a1bea2a7.jpg',
            'audioFile' => '67d15a1bea758.mp3',
        ],
        [
            'title' => 'Demo Work 02',
            'artist' => 'Liran Yuzbegi',
            'album' => 'Selected Works',
            'year' => 2024,
            'coverImage' => '67d15c4bea69b.png',
            'audioFile' => '67d15c4bea7f7.mp3',
        ],
        [
            'title' => 'Demo Work 03',
            'artist' => 'Liran Yuzbegi',
            'album' => 'Selected Works',
            'year' => 2025,
            'coverImage' => '67d2f8e737cc1.jpg',
            'audioFile' => '67d2f8e737db3.mp3',
        ],
        [
            'title' => 'Demo Work 04',
            'artist' => 'Liran Yuzbegi',
            'album' => 'Selected Works',
            'year' => 2025,
            'coverImage' => '67d2f91b10d19.jpg',
            'audioFile' => '67d2f91b1108a.mp3',
        ],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SongRepository $songRepository,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDirectory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Shows what would be inserted without modifying the database.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $inserted = 0;
        $skipped = 0;

        foreach (self::SONGS as $songData) {
            if ($this->songRepository->findOneBy(['audioFile' => $songData['audioFile']]) instanceof Song) {
                $io->writeln(sprintf('Skipped existing file: %s', $songData['audioFile']));
                ++$skipped;
                continue;
            }

            if (!$this->mediaFilesExist($songData['coverImage'], $songData['audioFile'])) {
                $io->error(sprintf(
                    'Missing cover or audio file for "%s". No database changes were saved.',
                    $songData['title'],
                ));

                return Command::FAILURE;
            }

            $io->writeln(sprintf('Adding: %s', $songData['title']));
            ++$inserted;

            if ($dryRun) {
                continue;
            }

            $song = (new Song())
                ->setTitle($songData['title'])
                ->setArtist($songData['artist'])
                ->setAlbum($songData['album'])
                ->setYear($songData['year'])
                ->setCoverImage($songData['coverImage'])
                ->setAudioFile($songData['audioFile']);

            $this->entityManager->persist($song);
        }

        if (!$dryRun) {
            $this->entityManager->flush();
        }

        $io->success(sprintf(
            '%s: %d song(s) ready to insert, %d already present.',
            $dryRun ? 'Dry run complete' : 'Seeding complete',
            $inserted,
            $skipped,
        ));

        return Command::SUCCESS;
    }

    private function mediaFilesExist(string $coverImage, string $audioFile): bool
    {
        return is_file($this->projectDirectory . '/public/uploads/covers/' . $coverImage)
            && is_file($this->projectDirectory . '/public/uploads/songs/' . $audioFile);
    }
}
