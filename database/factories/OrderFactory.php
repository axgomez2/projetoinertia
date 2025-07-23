<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . $this->faker->unique()->numerify('######'),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'subtotal' => $this->faker->randomFloat(2, 10, 500),
            'tax_amount' => $this->faker->randomFloat(2, 0, 50),
            'shipping_amount' => $this->faker->randomFloat(2, 0, 30),
            'discount_amount' => $this->faker->randomFloat(2, 0, 20),
            'total_amount' => function (array $attributes) {
                return $attributes['subtotal'] + $attributes['tax_amount'] + $attributes['shipping_amount'] - $attributes['discount_amount'];
            },
            'payment_method' => $this->faker->randomElement(['credit_card', 'debit_card', 'pix', 'boleto']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'shipping_address' => [
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'address_line_1' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => $this->faker->stateAbbr,
                'postal_code' => $this->faker->postcode,
                'country' => 'Brasil',
                'phone' => $this->faker->phoneNumber,
            ],
            'billing_address' => [
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'address_line_1' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => $this->faker->stateAbbr,
                'postal_code' => $this->faker->postcode,
                'country' => 'Brasil',
                'phone' => $this->faker->phoneNumber,
            ],
            'notes' => $this->faker->optional()->sentence,
            'processed_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'shipped_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'delivered_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
            'processed_at' => null,
            'shipped_at' => null,
            'delivered_at' => null,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'payment_status' => 'paid',
            'processed_at' => now(),
            'shipped_at' => null,
            'delivered_at' => null,
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
            'payment_status' => 'paid',
            'processed_at' => now()->subDays(2),
            'shipped_at' => now(),
            'delivered_at' => null,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
            'processed_at' => now()->subDays(5),
            'shipped_at' => now()->subDays(2),
            'delivered_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'payment_status' => 'failed',
            'processed_at' => null,
            'shipped_at' => null,
            'delivered_at' => null,
        ]);
    }
}
