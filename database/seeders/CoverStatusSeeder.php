<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CoverStatus;

class CoverStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['title' => 'Mint (M)', 'description' => 'Perfeito, sem defeitos'],
            ['title' => 'Near Mint (NM)', 'description' => 'Quase perfeito, sinais mínimos de manuseio'],
            ['title' => 'Very Good Plus (VG+)', 'description' => 'Muito bom, leves sinais de uso'],
            ['title' => 'Very Good (VG)', 'description' => 'Bom, sinais de uso mas estruturalmente sólido'],
            ['title' => 'Good Plus (G+)', 'description' => 'Razoável, uso moderado com pequenos danos'],
            ['title' => 'Good (G)', 'description' => 'Usado, danos visíveis mas ainda funcional'],
            ['title' => 'Poor (P)', 'description' => 'Muito danificado, apenas valor de coleção'],
            ['title' => 'No Cover', 'description' => 'Sem capa original'],
        ];

        foreach ($statuses as $status) {
            CoverStatus::create($status);
        }
    }
}
