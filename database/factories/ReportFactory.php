<?php

namespace Database\Factories;

use App\Enums\Community;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-15 months', 'now');

        return [
            'community' => Community::FAD,
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => fake()->boolean(30)
                ? (clone $start)->modify('+2 days')->format('Y-m-d')
                : null,
            'location' => fake()->city(),
        ];
    }
}
