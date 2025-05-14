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
use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Put(),
        new Post(),
        new Delete(),
        new Patch(),
    ],
)]
#[ApiResource(
    uriTemplate: '/users/{id}/conversations',
    operations: [new GetCollection()],
    uriVariables: [
        'id' => new Link(
            fromClass: User::class,
            fromProperty: 'conversations'
        )
    ],
    normalizationContext: ['groups' => ['user:conversations:read']],
)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:conversations:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[ApiProperty(required: true)]
    #[Groups(['user:conversations:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:conversations:read'])]
    private ?string $picturePath = 'picture_image_path_base.jpg';

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['user:conversations:read'])]
    private \DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'conversations')]
    #[Assert\Count(min: 2)]
    #[ApiProperty(required: true)]
    private Collection $users;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', cascade: ['remove'])]
    private Collection $messages;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPicturePath(): ?string
    {
        return $this->picturePath;
    }

    public function setPicturePath(string $picturePath): static
    {
        $this->picturePath = $picturePath;

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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addConversation($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeConversation($this);
        }

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
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        // set the owning side to null (unless already changed)
        if ($this->messages->removeElement($message) && $message->getConversation() === $this) {
            $message->setConversation(null);
        }

        return $this;
    }

    #[Groups(['user:conversations:read'])]
    public function getLastMessage(): ?Message
    {
        if ($this->messages->isEmpty()) {
            return null;
        }

        $messages = $this->messages->toArray();

        usort($messages, fn(Message $a, Message $b) => $b->getCreatedAt() <=> $a->getCreatedAt());

        return $messages[0] ?? null;
    }
}
