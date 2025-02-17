<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Machine;
use App\Models\Product;
use App\Models\MachineProduct;

class MachineSeeder extends Seeder
{
    public function run()
    {
        $machines = Machine::factory()->count(5)->create();

        $products = Product::all();

        foreach ($machines as $machine) {
            foreach ($products as $product) {
                MachineProduct::create([
                    'machine_id' => $machine->id,
                    'product_id' => $product->id,
                    'stock' => rand(5, 15),
                ]);
            }
        }
    }
}
