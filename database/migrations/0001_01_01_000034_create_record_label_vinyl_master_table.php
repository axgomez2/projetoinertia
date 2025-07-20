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
        Schema::create('record_label_vinyl_master', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_label_id');
            $table->unsignedBigInteger('vinyl_master_id');
            $table->timestamps();

            $table->foreign('record_label_id')->references('id')->on('record_labels')->onDelete('cascade');
            $table->foreign('vinyl_master_id')->references('id')->on('vinyl_masters')->onDelete('cascade');

            $table->unique(['record_label_id', 'vinyl_master_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_label_vinyl_master');
    }
};
