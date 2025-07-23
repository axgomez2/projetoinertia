<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraph,
            'product_type_id' => ProductType::factory(),
            'productable_id' => 1, // Default value
            'productable_type' => 'App\\Models\\VinylMaster', // Default type
        ];
    }
}
