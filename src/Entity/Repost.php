<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\DTO\RepostPatchDTO;
use App\Repository\RepostRepository;
use App\State\PatchRepostStateProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RepostRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Patch(
            uriTemplate: '/reposts/{id}',
            input: RepostPatchDTO::class,
            processor: PatchRepostStateProcessor::class,
        ),
        new Post(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['repost:read']],
    denormalizationContext: ['groups' => ['repost:write', 'repost:patch']],
)]
class Repost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['repost:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reposts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['repost:read', 'repost:write'])]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'reposts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['repost:read', 'repost:write'])]
    private ?Twit $twit = null;

    #[ORM\Column(length: 255)]
    #[Groups(['repost:read', 'repost:write', 'repost:patch'])]
    private ?string $comment = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['repost:read'])]
    private \DateTimeImmutable $createdAt;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
