<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('suppliers')->truncate();

        $suppliers = [
            [
                'name' => 'Fornecedor de Discos Nacionais',
                'phone' => '11987654321',
                'email' => 'contato@fornecedornacional.com',
                'document' => '12345678000199',
                'document_type' => 'cnpj',
                'address' => 'Rua dos Vinis, 123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567',
                'country' => 'Brasil',
                'website' => 'https://www.distribuidoranacional.com.br',
                'contact_person' => 'João Silva',
                'notes' => 'Fornecedor principal para lançamentos nacionais',
                'is_active' => true,
            ],
            [
                'name' => 'Importadora Premium',
                'company_name' => 'Premium Records Import & Export',
                'email' => 'imports@premiumrecords.com',
                'phone' => '(21) 2345-6789',
                'address' => 'Av. Atlântica, 456',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'zipcode' => '22000-000',
                'website' => 'https://importadosraros.com',
                'last_purchase_date' => Carbon::now()->subWeeks(2),
                'notes' => 'Fornecedor principal de Jazz e Blues importado.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Distribuidora Independente',
                'phone' => '3133334444',
                'email' => 'indie@distroindie.com.br',
                'document' => '11222333000144',
                'document_type' => 'cnpj',
                'address' => 'Rua dos Inconfidentes, 1000',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
                'zipcode' => '30140-120',
                'website' => null,
                'last_purchase_date' => Carbon::now()->subDays(5),
                'notes' => 'Foco em selos e artistas independentes.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        Supplier::insert($suppliers);
    }
}
