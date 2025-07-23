<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to pos_sales table for better performance
        if (Schema::hasTable('pos_sales')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->index(['user_id', 'created_at']);
                $table->index(['payment_method', 'created_at']);
                $table->index('invoice_number');
                $table->index('created_at');
            });
        }

        // Add indexes to pos_sale_items table
        if (Schema::hasTable('pos_sale_items')) {
            Schema::table('pos_sale_items', function (Blueprint $table) {
                $table->index(['pos_sale_id']);
                $table->index(['vinyl_sec_id']);
                $table->index(['pos_sale_id', 'vinyl_sec_id']);
            });
        }

        // Add indexes to cart_items table for better cart performance
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->index(['cart_id', 'created_at']);
                $table->index(['product_id']);
            });
        }

        // Add indexes to carts table
        if (Schema::hasTable('carts')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->index(['user_id', 'status']);
                $table->index(['status', 'updated_at']);
            });
        }

        // Add indexes to wishlists table for better wishlist queries
        if (Schema::hasTable('wishlists')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->index(['user_id', 'created_at']);
                $table->index(['product_id']);
            });
        }

        // Add indexes to wantlists table
        if (Schema::hasTable('wantlists')) {
            Schema::table('wantlists', function (Blueprint $table) {
                $table->index(['user_id', 'created_at']);
                $table->index(['product_id']);
            });
        }

        // Add indexes to products table for better search performance
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['productable_type', 'productable_id']);
                $table->index(['product_type_id']);
                $table->index(['name']);
                $table->index(['slug']);
            });
        }

        // Add indexes to users table for admin queries
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['role', 'created_at']);
                $table->index(['email_verified_at']);
                $table->index(['created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from pos_sales table
        if (Schema::hasTable('pos_sales')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'created_at']);
                $table->dropIndex(['payment_method', 'created_at']);
                $table->dropIndex(['invoice_number']);
                $table->dropIndex(['created_at']);
            });
        }

        // Remove indexes from pos_sale_items table
        if (Schema::hasTable('pos_sale_items')) {
            Schema::table('pos_sale_items', function (Blueprint $table) {
                $table->dropIndex(['pos_sale_id']);
                $table->dropIndex(['vinyl_sec_id']);
                $table->dropIndex(['pos_sale_id', 'vinyl_sec_id']);
            });
        }

        // Remove indexes from cart_items table
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropIndex(['cart_id', 'created_at']);
                $table->dropIndex(['product_id']);
            });
        }

        // Remove indexes from carts table
        if (Schema::hasTable('carts')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'status']);
                $table->dropIndex(['status', 'updated_at']);
            });
        }

        // Remove indexes from wishlists table
        if (Schema::hasTable('wishlists')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'created_at']);
                $table->dropIndex(['product_id']);
            });
        }

        // Remove indexes from wantlists table
        if (Schema::hasTable('wantlists')) {
            Schema::table('wantlists', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'created_at']);
                $table->dropIndex(['product_id']);
            });
        }

        // Remove indexes from products table
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['productable_type', 'productable_id']);
                $table->dropIndex(['product_type_id']);
                $table->dropIndex(['name']);
                $table->dropIndex(['slug']);
            });
        }

        // Remove indexes from users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['role', 'created_at']);
                $table->dropIndex(['email_verified_at']);
                $table->dropIndex(['created_at']);
            });
        }
    }
};
