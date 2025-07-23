<?php

namespace Database\Factories;

use App\Models\VinylMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VinylMaster>
 */
class VinylMasterFactory extends Factory
{
    protected $model = VinylMaster::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->words(3, true);

        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'discogs_id' => $this->faker->optional()->numerify('########'),
            'description' => $this->faker->optional()->paragraph(),
            'cover_image' => $this->faker->optional()->imageUrl(300, 300, 'music'),
            'discogs_url' => $this->faker->optional()->url(),
            'release_year' => $this->faker->numberBetween(1950, 2024),
            'country' => $this->faker->countryCode(),
        ];
    }

    /**
     * Indicate that the vinyl master is a classic album.
     */
    public function classic(): static
    {
        return $this->state(fn (array $attributes) => [
            'release_year' => $this->faker->numberBetween(1960, 1990),
        ]);
    }

    /**
     * Indicate that the vinyl master is a recent release.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'release_year' => $this->faker->numberBetween(2020, 2024),
        ]);
    }

    /**
     * Indicate that the vinyl master has a Discogs ID.
     */
    public function withDiscogs(): static
    {
        return $this->state(fn (array $attributes) => [
            'discogs_id' => $this->faker->numerify('########'),
            'discogs_url' => 'https://www.discogs.com/release/' . $this->faker->numerify('########'),
        ]);
    }
}
