<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use App\Repository\LikeRepository;
use App\State\LikeToggleProcessor;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikeRepository::class)]
#[ORM\Table(name: '`like`')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Delete(),
        new Post(
            uriTemplate: '/twits/{twit_id}/like',
            uriVariables: [
                'twit_id' => new Link(fromProperty: 'likes', fromClass: Twit::class),
            ],
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Like added or removed',
                        content: new \ArrayObject([
                            'application/ld+json' => [
                                'schema' => [
                                    'oneOf' => [
                                        ['$ref' => '#/components/schemas/Like'],
                                        ['type' => 'null', 'description' => 'Returned when unliked'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Twit not found',
                    ),
                    '403' => new Response(
                        description: 'Authentication required',
                    ),
                ],
                summary: 'Toggle like on a Twit',
                description: 'Toggles a like for the authenticated user on the given Twit. If already liked, it unlikes. If not liked, it creates a new like.',
            ),
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            input: false,
            processor: LikeToggleProcessor::class,
        ),
    ],
)]
class Like
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    #[ApiProperty(required: true)]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    #[ApiProperty(required: true)]
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
