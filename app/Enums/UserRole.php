<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case ANGGOTA = 'anggota';

    public function label(): string
    {
        return $this === self::ADMIN ? 'Admin' : 'Anggota';
    }
}
