<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MidiaStatus;

class MidiaStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['title' => 'Mint (M)', 'description' => 'Perfeito, nunca foi tocado'],
            ['title' => 'Near Mint (NM)', 'description' => 'Quase perfeito, tocado poucas vezes'],
            ['title' => 'Very Good Plus (VG+)', 'description' => 'Muito bom, sinais leves de uso'],
            ['title' => 'Very Good (VG)', 'description' => 'Bom, sinais visíveis de uso mas sem afetar a reprodução'],
            ['title' => 'Good Plus (G+)', 'description' => 'Razoável, uso moderado com alguns ruídos'],
            ['title' => 'Good (G)', 'description' => 'Usado, ruídos perceptíveis mas ainda reproduzível'],
            ['title' => 'Poor (P)', 'description' => 'Muito danificado, apenas para colecionadores'],
        ];

        foreach ($statuses as $status) {
            MidiaStatus::create($status);
        }
    }
}
