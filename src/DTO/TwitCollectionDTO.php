<?php

namespace App\DTO;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    shortName: 'TwitCollectionOutput',
    description: 'Twit collection custom output DTO',
    operations: [],
)]
class TwitCollectionDTO
{
    public function __construct(#[ApiProperty]
        public ?int $id, #[ApiProperty]
        public ?string $content, #[ApiProperty]
        public string $author, #[ApiProperty]
        public string $createdAt, #[ApiProperty]
        public bool $isLikedByUser, #[ApiProperty]
        public bool $isRepostedByUser, #[ApiProperty]
        public int $nbLikes, #[ApiProperty]
        public int $nbReposts, #[ApiProperty]
        public int $nbComments)
    {
    }
}
