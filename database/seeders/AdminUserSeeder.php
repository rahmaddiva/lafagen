<?php

namespace Database\Seeders;

use App\Enums\Community;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@lafagen.test'],
            [
                'name' => 'Admin FAD',
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'password')),
                'community' => Community::FAD,
                'role' => UserRole::ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin-genre@lafagen.test'],
            [
                'name' => 'Admin GENRE',
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'password')),
                'community' => Community::GENRE,
                'role' => UserRole::ADMIN,
            ]
        );
    }
}
