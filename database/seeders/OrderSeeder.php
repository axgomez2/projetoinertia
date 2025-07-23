<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a test user
        $user = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Test Customer',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Get or create a test product
        $product = Product::first();
        if (!$product) {
            $product = Product::create([
                'name' => 'Test Vinyl Record',
                'description' => 'A test vinyl record for demonstration',
                'price' => 29.99,
                'stock_quantity' => 10,
                'status' => 'active',
            ]);
        }

        // Create sample orders
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentStatuses = ['pending', 'paid', 'failed'];

        for ($i = 1; $i <= 10; $i++) {
            $status = $statuses[array_rand($statuses)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

            $subtotal = rand(2000, 10000) / 100; // Random price between 20.00 and 100.00
            $taxAmount = $subtotal * 0.1; // 10% tax
            $shippingAmount = 15.00;
            $discountAmount = 0;
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

            $order = Order::create([
                'order_number' => 'ORD-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'status' => $status,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'payment_method' => 'credit_card',
                'payment_status' => $paymentStatus,
                'shipping_address' => [
                    'first_name' => 'João',
                    'last_name' => 'Silva',
                    'address_line_1' => 'Rua das Flores, 123',
                    'address_line_2' => 'Apt 45',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'postal_code' => '01234-567',
                    'country' => 'BR',
                    'phone' => '(11) 99999-9999',
                ],
                'billing_address' => [
                    'first_name' => 'João',
                    'last_name' => 'Silva',
                    'address_line_1' => 'Rua das Flores, 123',
                    'address_line_2' => 'Apt 45',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'postal_code' => '01234-567',
                    'country' => 'BR',
                    'phone' => '(11) 99999-9999',
                ],
                'notes' => $i % 3 === 0 ? 'Entrega urgente solicitada pelo cliente' : null,
                'processed_at' => in_array($status, ['processing', 'shipped', 'delivered']) ? now()->subDays(rand(1, 5)) : null,
                'shipped_at' => in_array($status, ['shipped', 'delivered']) ? now()->subDays(rand(1, 3)) : null,
                'delivered_at' => $status === 'delivered' ? now()->subDays(rand(0, 2)) : null,
                'created_at' => now()->subDays(rand(0, 30)),
            ]);

            // Create order items
            $quantity = rand(1, 3);
            $itemPrice = $subtotal / $quantity;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $itemPrice,
                'quantity' => $quantity,
                'item_total' => $itemPrice * $quantity,
            ]);

            // Create status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $status,
                'previous_status' => null,
                'notes' => 'Pedido criado',
                'changed_by' => null,
                'created_at' => $order->created_at,
            ]);

            // Add additional status changes for processed orders
            if (in_array($status, ['processing', 'shipped', 'delivered'])) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'processing',
                    'previous_status' => 'pending',
                    'notes' => 'Pedido em processamento',
                    'changed_by' => null,
                    'created_at' => $order->processed_at,
                ]);
            }

            if (in_array($status, ['shipped', 'delivered'])) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'shipped',
                    'previous_status' => 'processing',
                    'notes' => 'Pedido enviado',
                    'changed_by' => null,
                    'created_at' => $order->shipped_at,
                ]);
            }

            if ($status === 'delivered') {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'delivered',
                    'previous_status' => 'shipped',
                    'notes' => 'Pedido entregue',
                    'changed_by' => null,
                    'created_at' => $order->delivered_at,
                ]);
            }
        }
    }
}
