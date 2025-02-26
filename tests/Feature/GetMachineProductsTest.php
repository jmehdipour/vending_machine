<?php

namespace Tests\Feature;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Machine;
use App\Models\MachineProduct;
use App\Models\Product;

class GetMachineProductsTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetMachineProducts_WhenMachineDoesNotExist_Returns404Error()
    {
        $response = $this->get('/api/machines/1/products');

       $response->seeStatusCode(404)->seeJson(['error' => 'Machine not found']);
    }
    public function testGetMachineProducts_WhenProductsExist_Success()
    {
        $machine = Machine::factory()->create();
        $product = Product::create(['name' => 'Soda']);
        MachineProduct::create([
            'machine_id' => $machine->id,
            'product_id' => $product->id,
            'stock' => 10
        ]);

        $response = $this->get("/api/machines/{$machine->id}/products");

        $response->seeStatusCode(200)
            ->seeJson([
                'id' => 1,
                'machine_id' => $machine->id,
                'product_id' => $product->id,
                'stock' => 10,
                'name' => 'Soda'
            ]);
    }

    public function testGetMachineProducts_WhenNoProductsExist_ReturnsEmptyArray()
    {
        $machine = Machine::factory()->create();

        $response = $this->get("/api/machines/{$machine->id}/products");

        $response->seeStatusCode(200)
            ->seeJsonEquals([]);
    }
}
