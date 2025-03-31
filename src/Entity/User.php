<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\UserRepository;
use App\State\PostUserStateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(processor: PostUserStateProcessor::class),
        new Put(),
        new Delete(),
        new Patch(),
    ],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 20, nullable: true)] // Must be set after account creation but not on first POST so user can be onboarded
    private ?string $username = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)] // Must be set after account creation but not on first POST so user can be onboarded
    #[Assert\LessThanOrEqual(value: '-13 years', message: 'You must be at least 13 years old.')]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(length: 255)]
    private ?string $profileImgPath = 'profile_image_path_base.jpg';

    #[ORM\Column(nullable: false)]
    private bool $private = false;

    #[ORM\Column(nullable: false)]
    private bool $active = true;

    #[ORM\Column(nullable: false)]
    private bool $banned = false;

    /**
     * @var Collection<int, Twit>
     */
    #[ORM\OneToMany(targetEntity: Twit::class, mappedBy: 'author')]
    private Collection $twits;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\ManyToMany(targetEntity: Conversation::class, inversedBy: 'users')]
    private Collection $conversations;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'author')]
    private Collection $messages;

    /**
     * @var Collection<int, Follow>
     * A collection of Follow object that has $this for $followed
     */
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'followed')]
    private Collection $followers;

    /**
     * @var Collection<int, Follow>
     * A collection of Follow object that has $this for $follower
     */
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'follower')]
    private Collection $followings;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'author')]
    private Collection $likes;

    /**
     * @var Collection<int, Repost>
     */
    #[ORM\OneToMany(targetEntity: Repost::class, mappedBy: 'author')]
    private Collection $reposts;

    public function __construct()
    {
        $this->twits = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->followings = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->reposts = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    #[\Override]
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    #[\Override]
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getProfileImgPath(): ?string
    {
        return $this->profileImgPath;
    }

    public function setProfileImgPath(string $profileImgPath): static
    {
        $this->profileImgPath = $profileImgPath;

        return $this;
    }

    #[\Override]
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): static
    {
        $this->private = $private;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function isBanned(): bool
    {
        return $this->banned;
    }

    public function setBanned(bool $banned): static
    {
        $this->banned = $banned;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return Collection<int, Twit>
     */
    public function getTwits(): Collection
    {
        return $this->twits;
    }

    public function addTwit(Twit $twit): static
    {
        if (!$this->twits->contains($twit)) {
            $this->twits->add($twit);
            $twit->setAuthor($this);
        }

        return $this;
    }

    public function removeTwit(Twit $twit): static
    {
        // set the owning side to null (unless already changed)
        if ($this->twits->removeElement($twit) && $twit->getAuthor() === $this) {
            $twit->setAuthor(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        $this->conversations->removeElement($conversation);

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setAuthor($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        // set the owning side to null (unless already changed)
        if ($this->messages->removeElement($message) && $message->getAuthor() === $this) {
            $message->setAuthor(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Follow>
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(Follow $follower): static
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
            $follower->setFollowed($this);
        }

        return $this;
    }

    public function removeFollower(Follow $follower): static
    {
        // set the owning side to null (unless already changed)
        if ($this->followers->removeElement($follower) && $follower->getFollower() === $this) {
            $follower->setFollower(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Follow>
     */
    public function getFollowings(): Collection
    {
        return $this->followings;
    }

    public function addFollowing(Follow $following): static
    {
        if (!$this->followings->contains($following)) {
            $this->followings->add($following);
            $following->setFollower($this);
        }

        return $this;
    }

    public function removeFollowing(Follow $following): static
    {
        // set the owning side to null (unless already changed)
        if ($this->followings->removeElement($following) && $following->getFollowed() === $this) {
            $following->setFollowed(null);
        }

        return $this;
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
            $like->setAuthor($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        // set the owning side to null (unless already changed)
        if ($this->likes->removeElement($like) && $like->getAuthor() === $this) {
            $like->setAuthor(null);
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
            $repost->setAuthor($this);
        }

        return $this;
    }

    public function removeRepost(Repost $repost): static
    {
        // set the owning side to null (unless already changed)
        if ($this->reposts->removeElement($repost) && $repost->getAuthor() === $this) {
            $repost->setAuthor(null);
        }

        return $this;
    }
}
