<?php

namespace Tests\Unit\Repositories;

use App\Models\Machine;
use App\Models\Product;
use App\Models\MachineProduct;
use App\Repositories\ProductRepository;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    protected $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository();
    }

    public function testGetProductsByMachine_WhenValidMachineId_ReturnsProducts()
    {
        $machine = Machine::factory()->create();
        $product = Product::factory()->create();
        MachineProduct::factory()->create([
            'machine_id' => $machine->id,
            'product_id' => $product->id,
            'stock' => 10,
        ]);

        $products = $this->repository->getProductsByMachine($machine->id);

        $this->assertCount(1, $products);
        $this->assertEquals($product->id, $products[0]->product->id);
        $this->assertEquals($machine->id, $products[0]->machine_id);
    }

    /**
     * Test decrementing product stock.
     */
    public function testDecrementProductStock_WhenValidData_DecreasesStock()
    {
        $machine = Machine::factory()->create();
        $product = Product::factory()->create();
        $machineProduct = MachineProduct::factory()->create([
            'machine_id' => $machine->id,
            'product_id' => $product->id,
            'stock' => 10,
        ]);

        $this->repository->decrementProductStock($machine->id, $product->id);

        $machineProduct->refresh();
        $this->assertEquals(9, $machineProduct->stock);
    }

    public function testDecrementProductStock_WhenStockIsZero_DoesNotGoNegative()
    {
        $machine = Machine::factory()->create();
        $product = Product::factory()->create();
        $machineProduct = MachineProduct::factory()->create([
            'machine_id' => $machine->id,
            'product_id' => $product->id,
            'stock' => 0,
        ]);

        $this->repository->decrementProductStock($machine->id, $product->id);

        $machineProduct->refresh();
        $this->assertEquals(0, $machineProduct->stock);
    }

    /**
     * Test getting product stock in a machine.
     */
    public function testGetProductStockInMachine_WhenValidData_ReturnsStock()
    {
        // Arrange: Create a machine, a product, and associate them
        $machine = Machine::factory()->create();
        $product = Product::factory()->create();
        MachineProduct::factory()->create([
            'machine_id' => $machine->id,
            'product_id' => $product->id,
            'stock' => 15,
        ]);

        $stock = $this->repository->getProductStockInMachine($machine->id, $product->id);

        $this->assertEquals(15, $stock);
    }

    public function testGetProductStockInMachine_WhenProductNotFound_ReturnsZero()
    {
        $machine = Machine::factory()->create();

        $stock = $this->repository->getProductStockInMachine($machine->id, 999);

        $this->assertEquals(0, $stock);
    }
}
