<?php

namespace Database\Factories;

use App\Models\OrderStatusHistory;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStatusHistoryFactory extends Factory
{
    protected $model = OrderStatusHistory::class;

    public function definition(): array
    {
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $currentStatus = $this->faker->randomElement($statuses);
        $previousStatus = $this->faker->optional()->randomElement(array_diff($statuses, [$currentStatus]));

        return [
            'order_id' => Order::factory(),
            'status' => $currentStatus,
            'previous_status' => $previousStatus,
            'notes' => $this->faker->optional()->sentence,
            'changed_by' => User::factory(),
        ];
    }
}
