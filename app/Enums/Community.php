<?php

namespace App\Enums;

enum Community: string
{
    case FAD = 'fad';
    case GENRE = 'genre';

    public function config(): array
    {
        return config("communities.{$this->value}");
    }
}
