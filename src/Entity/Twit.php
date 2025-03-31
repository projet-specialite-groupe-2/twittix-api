<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Enum\TwitStatus;
use App\Repository\TwitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: TwitRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Patch(),
    ],
)] class Twit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[ApiProperty(required: true)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'twits')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(
        required: true,
        openapiContext: [
            'example' => '/api/users/1',
            'description' => "IRI (identifiant de ressource) de l'auteur du post",
            'type' => 'string',
        ]
    )]
    private ?User $author = null;

    #[ORM\Column(length: 255, enumType: TwitStatus::class)]
    private ?TwitStatus $status = TwitStatus::PUBLISHED;

    #[ORM\Column(nullable: true)]
    private ?int $parent = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'twit')]
    private Collection $likes;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
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

    public function getParent(): ?int
    {
        return $this->parent;
    }

    public function setParent(?int $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getStatus(): ?TwitStatus
    {
        return $this->status;
    }

    public function setStatus(?TwitStatus $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setTwit($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        // set the owning side to null (unless already changed)
        if ($this->likes->removeElement($like) && $like->getTwit() === $this) {
            $like->setTwit(null);
        }

        return $this;
    }
}
