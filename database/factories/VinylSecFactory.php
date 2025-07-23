<?php

namespace Database\Factories;

use App\Models\VinylSec;
use App\Models\VinylMaster;
use App\Models\MidiaStatus;
use App\Models\CoverStatus;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VinylSec>
 */
class VinylSecFactory extends Factory
{
    protected $model = VinylSec::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 20, 200);
        $isPromotional = $this->faker->boolean(20); // 20% chance of being promotional
        $promotionalPrice = $isPromotional ? $price * 0.8 : null; // 20% discount

        return [
            'vinyl_master_id' => VinylMaster::factory(),
            'catalog_number' => $this->faker->bothify('??-####'),
            'barcode' => $this->faker->optional()->numerify('############'),
            'internal_code' => VinylSec::generateInternalCode(),
            'weight_id' => null, // Assuming Weight model exists but not required for tests
            'dimension_id' => null, // Assuming Dimension model exists but not required for tests
            'midia_status_id' => MidiaStatus::factory(),
            'cover_status_id' => CoverStatus::factory(),
            'supplier_id' => Supplier::factory(),
            'stock' => $this->faker->numberBetween(0, 50),
            'price' => $price,
            'format' => $this->faker->randomElement(['LP', 'EP', '7"', '12"', 'Box Set']),
            'num_discs' => $this->faker->numberBetween(1, 3),
            'speed' => $this->faker->randomElement(['33 1/3', '45', '78']),
            'edition' => $this->faker->optional()->randomElement(['Limited', 'Reissue', 'Original', 'Deluxe']),
            'notes' => $this->faker->optional()->sentence(),
            'is_new' => $this->faker->boolean(80), // 80% chance of being new
            'buy_price' => $price * 0.6, // 60% of selling price
            'promotional_price' => $promotionalPrice,
            'is_promotional' => $isPromotional,
            'promo_starts_at' => $isPromotional ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'promo_ends_at' => $isPromotional ? $this->faker->dateTimeBetween('now', '+1 month') : null,
            'in_stock' => function (array $attributes) {
                return $attributes['stock'] > 0;
            },
            'is_presale' => $this->faker->boolean(10), // 10% chance of being presale
            'presale_arrival_date' => function (array $attributes) {
                return $attributes['is_presale'] ? $this->faker->dateTimeBetween('now', '+3 months') : null;
            },
        ];
    }

    /**
     * Indicate that the vinyl is in stock.
     */
    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $this->faker->numberBetween(1, 50),
            'in_stock' => true,
        ]);
    }

    /**
     * Indicate that the vinyl is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
            'in_stock' => false,
        ]);
    }

    /**
     * Indicate that the vinyl is on promotion.
     */
    public function onPromotion(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price'] ?? 50.00;
            return [
                'is_promotional' => true,
                'promotional_price' => $price * 0.8, // 20% discount
                'promo_starts_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
                'promo_ends_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            ];
        });
    }

    /**
     * Indicate that the vinyl is a presale item.
     */
    public function presale(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_presale' => true,
            'presale_arrival_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'stock' => 0,
            'in_stock' => false,
        ]);
    }

    /**
     * Indicate that the vinyl is used.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_new' => false,
        ]);
    }

    /**
     * Set a specific price for the vinyl.
     */
    public function price(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
            'buy_price' => $price * 0.6,
        ]);
    }

    /**
     * Set a specific stock quantity.
     */
    public function stock(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $quantity,
            'in_stock' => $quantity > 0,
        ]);
    }
}
