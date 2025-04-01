<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class RepostPatchDTO
{
    #[Assert\NotBlank]
    #[Groups(['repost:read', 'repost:patch'])]
    public ?string $comment = null;
}
