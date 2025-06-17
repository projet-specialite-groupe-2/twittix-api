<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use App\DTO\RepostCommentDTO;
use App\Repository\RepostRepository;
use App\State\RepostCreateProcessor;
use App\State\RepostDeleteProcessor;
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
            normalizationContext: ['groups' => ['repost:read']],
            denormalizationContext: ['groups' => ['repost:write']],
        ),
        new Post(),
        new Delete(),
        new Delete(
            uriTemplate: '/twits/{twit_id}/repost',
            uriVariables: [
                'twit_id' => new Link(fromProperty: 'reposts', fromClass: Twit::class),
            ],
            openapi: new Operation(
                responses: [
                    '204' => new Response(
                        description: 'Repost successfully deleted',
                    ),
                    '404' => new Response(
                        description: 'Repost not found',
                    ),
                    '403' => new Response(
                        description: 'Authentication required',
                    ),
                ],
                summary: "Delete the current user's repost of a Twit",
                description: 'Allows an authenticated user to remove their repost for a given Twit. Returns 404 if no repost exists.',
            ),
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            processor: RepostDeleteProcessor::class,
        ),
        new Post(
            uriTemplate: '/twits/{twit_id}/repost',
            uriVariables: [
                'twit_id' => new Link(fromProperty: 'reposts', fromClass: Twit::class),
            ],
            openapi: new Operation(
                responses: [
                    '201' => new Response(
                        description: 'Repost successfully created',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Repost',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Response(
                        description: 'Repost already exists or validation error',
                    ),
                    '404' => new Response(
                        description: 'Twit not found',
                    ),
                    '403' => new Response(
                        description: 'Authentication required',
                    ),
                ],
                summary: 'Create a repost with a comment for a Twit',
                requestBody: new RequestBody(
                    description: 'The comment to include with the repost',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'comment' => ['type' => 'string'],
                                ],
                                'required' => ['comment'],
                            ],
                            'example' => [
                                'comment' => 'Check this out!',
                            ],
                        ],
                    ]),
                ),
            ),
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            input: RepostCommentDTO::class,
            output: Repost::class,
            name: 'repost_create',
            processor: RepostCreateProcessor::class,
        ),
    ],
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
    #[Groups(['repost:read'])]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'reposts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['repost:read'])]
    private ?Twit $twit = null;

    #[ORM\Column(length: 255)]
    #[Groups(['repost:read', 'repost:write'])]
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

    public function setComment(?string $comment): static
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
