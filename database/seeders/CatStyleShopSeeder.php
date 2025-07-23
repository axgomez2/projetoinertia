<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CatStyleShop;

class CatStyleShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Rock', 'slug' => 'rock'],
            ['name' => 'Pop', 'slug' => 'pop'],
            ['name' => 'Jazz', 'slug' => 'jazz'],
            ['name' => 'Blues', 'slug' => 'blues'],
            ['name' => 'Clássica', 'slug' => 'classica'],
            ['name' => 'Eletrônica', 'slug' => 'eletronica'],
            ['name' => 'Hip-Hop', 'slug' => 'hip-hop'],
            ['name' => 'Reggae', 'slug' => 'reggae'],
            ['name' => 'MPB', 'slug' => 'mpb'],
            ['name' => 'Samba', 'slug' => 'samba'],
            ['name' => 'Bossa Nova', 'slug' => 'bossa-nova'],
            ['name' => 'Country', 'slug' => 'country'],
            ['name' => 'Metal', 'slug' => 'metal'],
            ['name' => 'Punk', 'slug' => 'punk'],
            ['name' => 'Indie', 'slug' => 'indie'],
            ['name' => 'Raridades', 'slug' => 'raridades'],
            ['name' => 'Reedições', 'slug' => 'reedicoes'],
            ['name' => 'Importados', 'slug' => 'importados'],
        ];

        foreach ($categories as $category) {
            CatStyleShop::create($category);
        }
    }
}
