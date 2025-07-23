<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'document' => $this->faker->numerify('##.###.###/####-##'),
            'document_type' => 'cnpj',
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zipcode' => $this->faker->postcode(),
            'website' => $this->faker->url(),
            'last_purchase_date' => $this->faker->optional()->date(),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
