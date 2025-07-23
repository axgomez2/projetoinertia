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
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->uuid('user_id')->nullable(); // Nullable for anonymous views
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamp('viewed_at');
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes for performance optimization
            $table->index(['product_id', 'viewed_at']);
            $table->index(['user_id', 'viewed_at']);
            $table->index(['ip_address', 'viewed_at']);
            $table->index('viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};
