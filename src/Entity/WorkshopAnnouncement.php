<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class WorkshopAnnouncement
{
    public const HOMEPAGE_ID = 1;

    #[ORM\Id]
    #[ORM\Column]
    private int $id = self::HOMEPAGE_ID;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $title = 'Workshop: Slowness Aesthetics: Perceptions of Time in Audiovisual Work';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $venue = 'Department of Music Technology, Sibelius Academy, University of the Arts Helsinki';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $period = 'November–December 2026';
}
