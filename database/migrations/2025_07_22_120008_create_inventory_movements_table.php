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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vinyl_sec_id');
            $table->enum('type', ['in', 'out', 'adjustment', 'reserved', 'unreserved']);
            $table->integer('quantity'); // Positive for in/unreserved, negative for out/reserved
            $table->integer('previous_stock');
            $table->integer('new_stock');
            $table->string('reason')->nullable(); // sale, purchase, adjustment, return, etc.
            $table->text('notes')->nullable();
            $table->uuid('user_id')->nullable(); // Who made the movement
            $table->string('reference_type')->nullable(); // order, pos_sale, adjustment
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the reference
            $table->timestamps();

            // Foreign keys
            $table->foreign('vinyl_sec_id')->references('id')->on('vinyl_secs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes for performance
            $table->index(['vinyl_sec_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
