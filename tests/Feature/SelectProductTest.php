<?php


namespace Tests\Feature;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Machine;
use App\Models\MachineProduct;
use App\Enums\MachineStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SelectProductTest extends TestCase
{
    use DatabaseMigrations;

    public function testSelectProduct_DispensesProduct_WhenStockAvailableAndMachineProcessing()
    {
        $machine = Machine::factory()->create(['status' => MachineStatus::PROCESSING]);
        $productId = DB::table('products')->insertGetId([
            'name' => 'Test Product',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        MachineProduct::create([
            'machine_id' => $machine->id,
            'product_id' => $productId,
            'stock' => 5
        ]);

        $response = $this->post("/api/machines/{$machine->id}/select-product", [
            'product_id' => $productId
        ]);

        $response->seeStatusCode(200)
            ->seeJson(['message' => 'Product dispensed successfully']);
        $this->seeInDatabase('machines', [
            'id' => $machine->id,
            'status' => MachineStatus::IDLE
        ]);
        $this->seeInDatabase('machine_products', [
            'machine_id' => $machine->id,
            'product_id' => $productId,
            'stock' => 4
        ]);
    }

    public function testSelectProduct_ReturnsNotFound_WhenMachineDoesNotExist()
    {
        $response = $this->post("/api/machines/999/select-product", [
            'product_id' => 1
        ]);

        $response->seeStatusCode(404)
            ->seeJson(['error' => 'Machine not found']);
    }

    public function testSelectProduct_ReturnsError_WhenMachineNotProcessing()
    {
        $machine = Machine::factory()->create(['status' => MachineStatus::IDLE]);
        $productId = DB::table('products')->insertGetId([
            'name' => 'Test Product',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->post("/api/machines/{$machine->id}/select-product", [
            'product_id' => $productId
        ]);

        $response->seeStatusCode(400)
            ->seeJson(['error' => 'Please insert a coin first']);
    }

    public function testSelectProduct_ReturnsError_WhenProductOutOfStock()
    {
        $machine = Machine::factory()->create(['status' => MachineStatus::PROCESSING]);
        $productId = DB::table('products')->insertGetId([
            'name' => 'Test Product',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        MachineProduct::create([
            'machine_id' => $machine->id,
            'product_id' => $productId,
            'stock' => 0
        ]);

        $response = $this->post("/api/machines/{$machine->id}/select-product", [
            'product_id' => $productId
        ]);

        $response->seeStatusCode(400)
            ->seeJson(['error' => 'Product out of stock']);
        $this->seeInDatabase('machine_products', [
            'machine_id' => $machine->id,
            'product_id' => $productId,
            'stock' => 0
        ]);
    }

    public function testSelectProduct_ReturnsError_WhenProductIdNotProvided()
    {
        $machine = Machine::factory()->create(['status' => MachineStatus::PROCESSING]);

        $response = $this->post("/api/machines/{$machine->id}/select-product", []);

        $response->seeStatusCode(400)
            ->seeJson(['error' => 'Product ID is required']);
    }
}
