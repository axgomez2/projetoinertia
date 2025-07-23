<?php

namespace Database\Factories;

use App\Models\PosSale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PosSale>
 */
class PosSaleFactory extends Factory
{
    protected $model = PosSale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_name' => $this->faker->name(),
            'subtotal' => $this->faker->randomFloat(2, 10, 500),
            'discount' => $this->faker->randomFloat(2, 0, 50),
            'shipping' => 0, // POS sales don't have shipping
            'total' => function (array $attributes) {
                return $attributes['subtotal'] - $attributes['discount'];
            },
            'payment_method' => $this->faker->randomElement(['money', 'credit_card', 'debit_card', 'pix', 'bank_transfer']),
            'notes' => $this->faker->optional()->sentence(),
            'invoice_number' => function () {
                return PosSale::generateInvoiceNumber();
            },
        ];
    }

    /**
     * Indicate that the sale is paid with cash.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'money',
        ]);
    }

    /**
     * Indicate that the sale is paid with credit card.
     */
    public function creditCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'credit_card',
        ]);
    }

    /**
     * Indicate that the sale is paid with PIX.
     */
    public function pix(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'pix',
        ]);
    }

    /**
     * Indicate that the sale has no customer name.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_name' => null,
        ]);
    }

    /**
     * Indicate that the sale has no discount.
     */
    public function noDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount' => 0,
        ]);
    }
}
