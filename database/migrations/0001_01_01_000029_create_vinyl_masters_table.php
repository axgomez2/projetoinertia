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
        Schema::create('vinyl_masters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('discogs_id')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('discogs_url')->nullable();
            $table->year('release_year')->nullable();
            $table->string('country')->nullable();
            $table->unsignedBigInteger('record_label_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('record_label_id')->references('id')->on('record_labels')->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vinyl_masters');
    }
};
