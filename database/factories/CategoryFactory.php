<?php

namespace Database\Factories;

use App\Enums\Community;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'community' => Community::FAD,
            'name' => fake()->unique()->randomElement([
                'Pendidikan', 'Sosial', 'Lingkungan', 'Kewirausahaan',
                'Seni Budaya', 'Olahraga', 'Keagamaan', 'Keterampilan',
            ]),
        ];
    }
}
