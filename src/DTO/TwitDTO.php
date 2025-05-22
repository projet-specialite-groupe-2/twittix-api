<?php

namespace App\DTO;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    shortName: 'TwitCollectionOutput',
    description: 'Twit collection custom output DTO',
)]
class TwitDTO
{
    public function __construct(
        #[ApiProperty]
        public ?int $id,
        #[ApiProperty]
        public ?string $content,
        #[ApiProperty]
        public string $authorId,
        #[ApiProperty]
        public string $authorEmail,
        #[ApiProperty]
        public string $authorUsername,
        #[ApiProperty]
        public string $authorProfileImgPath,
        #[ApiProperty]
        public string $createdAt,
        #[ApiProperty]
        public bool $isLikedByUser,
        #[ApiProperty]
        public bool $isRepostedByUser,
        #[ApiProperty]
        public int $nbLikes,
        #[ApiProperty]
        public int $nbReposts,
        #[ApiProperty]
        public int $nbComments,
    ) {
    }
}
