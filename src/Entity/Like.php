<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\LikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikeRepository::class)]
#[ORM\Table(name: '`like`')]
#[ApiResource]
class Like
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    private ?Twit $twit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getTwit(): ?Twit
    {
        return $this->twit;
    }

    public function setTwit(?Twit $twit): static
    {
        $this->twit = $twit;

        return $this;
    }
}
