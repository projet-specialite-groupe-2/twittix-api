<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\DTO\TwitDTO;
use App\Enum\TwitStatus;
use App\Repository\TwitRepository;
use App\State\TwitCollectionCommentsProvider;
use App\State\TwitCollectionFollowersProvider;
use App\State\TwitCollectionProvider;
use App\State\TwitProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: TwitRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/twits/all',
            output: TwitDTO::class,
            name: 'get_twits_collection_custom',
            provider: TwitCollectionProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/twits/followings',
            name: 'get_twits_collection_followings',
            provider: TwitCollectionFollowersProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/twits/{id}/comments',
            uriVariables: [
                'id' => new Link(
                    fromProperty: 'id',
                    fromClass: Twit::class,
                ),
            ],
            name: 'get_twits_collection_comments',
            provider: TwitCollectionCommentsProvider::class,
        ),
        new Get(
            provider: TwitProvider::class,
        ),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
)] class Twit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 280)]
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
        ],
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

    /**
     * @var Collection<int, Repost>
     */
    #[ORM\OneToMany(targetEntity: Repost::class, mappedBy: 'twit')]
    private Collection $reposts;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'viewedTwits')]
    private Collection $viewers;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->reposts = new ArrayCollection();
        $this->viewers = new ArrayCollection();
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

    /**
     * @return Collection<int, Repost>
     */
    public function getReposts(): Collection
    {
        return $this->reposts;
    }

    public function addRepost(Repost $repost): static
    {
        if (!$this->reposts->contains($repost)) {
            $this->reposts->add($repost);
            $repost->setTwit($this);
        }

        return $this;
    }

    public function removeRepost(Repost $repost): static
    {
        // set the owning side to null (unless already changed)
        if ($this->reposts->removeElement($repost) && $repost->getTwit() === $this) {
            $repost->setTwit(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getViewers(): Collection
    {
        return $this->viewers;
    }

    public function addViewer(User $viewer): static
    {
        if (!$this->viewers->contains($viewer)) {
            $this->viewers->add($viewer);
            $viewer->addTwitViewedUser($this);
        }

        return $this;
    }

    public function removeViewer(User $viewer): static
    {
        if ($this->viewers->removeElement($viewer)) {
            $viewer->removeTwitViewedUser($this);
        }

        return $this;
    }
}
