<?php

namespace Database\Factories;

use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\VinylSec;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PosSaleItem>
 */
class PosSaleItemFactory extends Factory
{
    protected $model = PosSaleItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 10, 200);
        $quantity = $this->faker->numberBetween(1, 5);
        $itemDiscount = $this->faker->randomFloat(2, 0, $price * $quantity * 0.2); // Max 20% discount

        return [
            'pos_sale_id' => PosSale::factory(),
            'vinyl_sec_id' => VinylSec::factory(),
            'price' => $price,
            'quantity' => $quantity,
            'item_discount' => $itemDiscount,
            'item_total' => ($price * $quantity) - $itemDiscount,
        ];
    }

    /**
     * Indicate that the item has no discount.
     */
    public function noDiscount(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'item_discount' => 0,
                'item_total' => $attributes['price'] * $attributes['quantity'],
            ];
        });
    }

    /**
     * Indicate that the item has a specific quantity.
     */
    public function quantity(int $quantity): static
    {
        return $this->state(function (array $attributes) use ($quantity) {
            return [
                'quantity' => $quantity,
                'item_total' => ($attributes['price'] * $quantity) - $attributes['item_discount'],
            ];
        });
    }

    /**
     * Indicate that the item has a specific price.
     */
    public function price(float $price): static
    {
        return $this->state(function (array $attributes) use ($price) {
            return [
                'price' => $price,
                'item_total' => ($price * $attributes['quantity']) - $attributes['item_discount'],
            ];
        });
    }
}
