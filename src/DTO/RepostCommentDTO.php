<?php

namespace App\DTO;

use ApiPlatform\Metadata\ApiProperty;

class RepostCommentDTO
{

    public function __construct(#[ApiProperty] public string $comment)
    {
    }
}