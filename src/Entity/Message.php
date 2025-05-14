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
use App\Controller\Api\GetMessagesFromConversationController;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
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
    uriTemplate: '/conversations/{id}/messages',
    operations: [new GetCollection()],
    uriVariables: [
        'id' => new Link(
            fromClass: Conversation::class,
            fromProperty: 'messages'
        )
    ],
    normalizationContext: ['groups' => ['conversation:messages:read']],
)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['conversation:messages:read', 'user:conversations:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[ApiProperty(required: true)]
    #[Groups(['conversation:messages:read', 'user:conversations:read'])]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ApiProperty(required: true)]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ApiProperty(required: true)]
    #[Groups(['conversation:messages:read', 'user:conversations:read'])]
    private ?User $author = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups('conversation:messages:read')]
    private \DateTimeImmutable $createdAt;

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

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;
        if(!$conversation->getMessages()->contains($this)) {
            $conversation->addMessage($this);
        }

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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
