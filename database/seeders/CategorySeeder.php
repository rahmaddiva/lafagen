<?php

namespace Database\Seeders;

use App\Enums\Community;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public const DEFAULTS = [
        'Pendidikan',
        'Sosial & Kemasyarakatan',
        'Lingkungan Hidup',
        'Keagamaan',
        'Keterampilan',
        'Lainnya',
    ];

    public function run(): void
    {
        foreach (Community::cases() as $community) {
            foreach (self::DEFAULTS as $name) {
                Category::firstOrCreate([
                    'community' => $community,
                    'name' => $name,
                ]);
            }
        }
    }
}
