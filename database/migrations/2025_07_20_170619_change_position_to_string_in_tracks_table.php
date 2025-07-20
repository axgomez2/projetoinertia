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
        Schema::table('tracks', function (Blueprint $table) {
            // Altera o tipo da coluna para string para acomodar valores como 'A1', 'B2', etc.
            $table->string('position')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            // Reverte o tipo da coluna para integer. 
            // Aviso: isso pode causar perda de dados se houver valores não numéricos.
            $table->integer('position')->default(0)->change();
        });
    }
};
