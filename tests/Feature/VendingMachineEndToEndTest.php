<?php

namespace Tests\Feature;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Machine;
use App\Models\Product;
use App\Enums\MachineStatus;
use App\Models\MachineProduct;

class VendingMachineEndToEndTest extends TestCase
{
    use DatabaseMigrations;

    public function testEndToEnd_VendingMachineTransaction_SuccessfullyProcessesCoinAndDispensesProduct()
    {
        // Step 1: Create a machine in idle state
        $machine = Machine::factory()->create(['status' => MachineStatus::IDLE]);
        // Step 2: Create a product and associate it with the machine
        $product = Product::create(['name' => 'Soda']);
        MachineProduct::create([
            'machine_id' => $machine->id,
            'product_id' => $product->id,
            'stock' => 10
        ]);

        // Step 3: Insert coin into the vending machine (machine transitions to 'processing' state)
        $response = $this->post("/api/machines/{$machine->id}/insert-coin");
        // Assert coin insertion response is successful and machine status changed
        $response->seeStatusCode(200)
            ->seeJson(['message' => 'Coin inserted, you can select a product.']);
        $this->seeInDatabase('machines', [
            'id' => $machine->id,
            'status' => MachineStatus::PROCESSING
        ]);

        // Step 4: Retrieve products for the machine
        $response = $this->get("/api/machines/{$machine->id}/products");
        // Assert that the product is available in the machine
        $response->seeStatusCode(200)
            ->seeJson([
                'id' => $product->id,
                'name' => $product->name,
                'stock' => 10
            ]);

        // Step 5: Select a product
        $response = $this->post("/api/machines/{$machine->id}/select-product", ["product_id" => $product->id]);
        $response->seeStatusCode(200)
            ->seeJson(['message' => 'Product dispensed successfully']);
        $this->seeInDatabase('machine_products', [
            'machine_id' => $machine->id,
            'product_id' => $product->id,
            'stock' => 9  // stock decreased by 1 after dispensing
        ]);
        $this->seeInDatabase('transactions', [
            'machine_id' => $machine->id,
            'product_id' => $product->id
        ]);
    }
}
