<?php

namespace Database\Factories;

use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Artist>
 */
class ArtistFactory extends Factory
{
    protected $model = Artist::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'bio' => $this->faker->optional()->paragraph(),
            'country' => $this->faker->optional()->countryCode(),
            'formed_year' => $this->faker->optional()->numberBetween(1950, 2020),
            'discogs_id' => $this->faker->optional()->numerify('########'),
            'discogs_url' => $this->faker->optional()->url(),
        ];
    }

    /**
     * Indicate that the artist is a band.
     */
    public function band(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->words(2, true),
        ]);
    }

    /**
     * Indicate that the artist is a solo artist.
     */
    public function solo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->name(),
        ]);
    }

    /**
     * Indicate that the artist has a Discogs profile.
     */
    public function withDiscogs(): static
    {
        return $this->state(fn (array $attributes) => [
            'discogs_id' => $this->faker->numerify('########'),
            'discogs_url' => 'https://www.discogs.com/artist/' . $this->faker->numerify('########'),
        ]);
    }
}
