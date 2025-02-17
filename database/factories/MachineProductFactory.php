<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\Product;
use App\Models\MachineProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class MachineProductFactory extends Factory
{
    protected $model = MachineProduct::class;

    public function definition()
    {
        return [
            'machine_id' => Machine::factory(),
            'product_id' => Product::factory(),
            'stock' => $this->faker->numberBetween(1, 100),
        ];
    }
}
