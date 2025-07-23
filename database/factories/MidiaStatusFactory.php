<?php

namespace Database\Factories;

use App\Models\MidiaStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class MidiaStatusFactory extends Factory
{
    protected $model = MidiaStatus::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->randomElement([
                'Mint (M)', 'Near Mint (NM)', 'Very Good Plus (VG+)',
                'Very Good (VG)', 'Good Plus (G+)', 'Good (G)', 'Fair (F)', 'Poor (P)'
            ]),
            'description' => $this->faker->sentence(),
        ];
    }
}
