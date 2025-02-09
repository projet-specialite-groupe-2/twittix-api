<?php

namespace App\Enum;

enum PostStatus: string
{
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case DELETED = 'deleted';
}
