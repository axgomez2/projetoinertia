<?php

namespace Database\Factories;

use App\Models\CatStyleShop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CatStyleShopFactory extends Factory
{
    protected $model = CatStyleShop::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Rock', 'Pop', 'Jazz', 'Blues', 'Classical', 'Electronic',
            'Hip Hop', 'Reggae', 'Country', 'Folk', 'Punk', 'Metal'
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
