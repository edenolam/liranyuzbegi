<?php

namespace App\Repository;

use App\Entity\Song;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Song>
 */
class SongRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Song::class);
    }

    public function findLatestSongs(int $limit = 6): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.id', 'DESC') // Trier par ID décroissant (les plus récentes en premier)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findAllSongs(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.title', 'ASC') // Trier par titre alphabétiquement
            ->getQuery()
            ->getResult();
    }

}
