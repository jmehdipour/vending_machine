<?php

namespace Tests\Unit\Http\Controllers;

use App\Enums\MachineStatus;
use App\Http\Controllers\ProductController;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\MachineRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ProductControllerTest extends TestCase
{
    private $productRepository;
    private $machineRepository;
    private $transactionRepository;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->machineRepository = Mockery::mock(MachineRepositoryInterface::class);
        $this->transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);
        $this->controller = new ProductController($this->productRepository, $this->machineRepository, $this->transactionRepository);
    }

    public function testGetMachineProducts_WhenMachineDoesNotExist_Returns404Error() {
        $machineId = 123;
        $this->machineRepository->shouldReceive('findById')
            ->with($machineId)
            ->once()
            ->andReturn(null);

        $response = $this->controller->getMachineProducts($machineId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['error' => 'Machine not found'], $response->getData(true));
    }

    public function testGetMachineProducts_WhenProductsExist_ReturnsProductList()
    {
        $machineId = 1;
        $products = collect([
            (object)['id' => 1, 'machine_id' => $machineId, 'product_id' => 10, 'stock' => 5, 'product' => (object)['name' => 'Water']]
        ]);
        $this->machineRepository->shouldReceive('findById')
            ->with($machineId)
            ->once()
            ->andReturn((object)['id' => $machineId]);
        $this->productRepository->shouldReceive('getProductsByMachine')
            ->with($machineId)
            ->once()
            ->andReturn($products);

        $response = $this->controller->getMachineProducts($machineId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            ['id' => 1, 'machine_id' => 1, 'product_id' => 10, 'stock' => 5, 'name' => 'Water']
        ], $response->getData(true));
    }

    public function testGetMachineProducts_WhenNoProductsExist_ReturnsEmptyArray()
    {
        $machineId = 1;
        $this->machineRepository->shouldReceive('findById')
            ->with($machineId)
            ->once()
            ->andReturn((object)['id' => $machineId]);
        $this->productRepository->shouldReceive('getProductsByMachine')
            ->with($machineId)
            ->once()
            ->andReturn(collect([]));

        $response = $this->controller->getMachineProducts($machineId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getData(true));
    }

    public function testSelectProduct_WhenProductIsAvailable_UpdatesStockAndReturnsSuccess()
    {
        $machineId = 1;
        $productId = 10;
        $request = Request::create('/api/machines/1/select-product', 'POST', ['product_id' => $productId]);
        $this->machineRepository->shouldReceive('findById')
            ->with($machineId)
            ->once()
            ->andReturn((object)['status' => MachineStatus::PROCESSING->value]);
        $this->productRepository->shouldReceive('getProductStockInMachine')
            ->with($machineId, $productId)
            ->once()
            ->andReturn(3);
        $this->productRepository->shouldReceive('decrementProductStock')
            ->with($machineId, $productId)
            ->once();
        $this->machineRepository->shouldReceive('updateStatus')
            ->with($machineId, MachineStatus::IDLE->value)
            ->once();
        $this->transactionRepository->shouldReceive('createTransaction')
            ->with($machineId, $productId)
            ->once();
        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });

        $response = $this->controller->selectProduct($request, $machineId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['message' => 'Product dispensed successfully'], $response->getData(true));
    }

    public function testSelectProduct_WhenMachineNotFound_ReturnsNotFound()
    {
        $machineId = 1;
        $request = Request::create('/api/machines/1/select-product', 'POST', ['product_id' => 10]);
        $this->machineRepository->shouldReceive('findById')
            ->with($machineId)
            ->once()
            ->andReturn(null);

        $response = $this->controller->selectProduct($request, $machineId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['error' => 'Machine not found'], $response->getData(true));
    }

    public function testSelectProduct_WhenMachineIsIdle_ReturnsError()
    {
        $machineId = 1;
        $request = Request::create('/api/machines/1/select-product', 'POST', ['product_id' => 10]);
        $this->machineRepository->shouldReceive('findById')
            ->with($machineId)
            ->once()
            ->andReturn((object)['status' => MachineStatus::IDLE->value]);

        $response = $this->controller->selectProduct($request, $machineId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Please insert a coin first'], $response->getData(true));
    }

    public function testSelectProduct_WhenProductIsOutOfStock_ReturnsError()
    {
        $machineId = 1;
        $productId = 10;
        $request = Request::create('/api/machines/1/select-product', 'POST', ['product_id' => $productId]);
        $this->machineRepository->shouldReceive('findById')
            ->with($machineId)
            ->once()
            ->andReturn((object)['status' => MachineStatus::PROCESSING->value]);
        $this->productRepository->shouldReceive('getProductStockInMachine')
            ->with($machineId, $productId)
            ->once()
            ->andReturn(0);

        $response = $this->controller->selectProduct($request, $machineId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Product out of stock'], $response->getData(true));
    }

    public function testSelectProduct_WhenDecrementStockFails_RollsBackTransaction()
    {
        $machineId = 1;
        $productId = 10;
        $request = Request::create('/api/machines/1/select-product', 'POST', ['product_id' => $productId]);
        $this->machineRepository->shouldReceive('findById')
            ->with($machineId)
            ->once()
            ->andReturn((object)['status' => MachineStatus::PROCESSING->value]);
        $this->productRepository->shouldReceive('getProductStockInMachine')
            ->with($machineId, $productId)
            ->once()
            ->andReturn(3);
        $this->productRepository->shouldReceive('decrementProductStock')
            ->with($machineId, $productId)
            ->once()
            ->andThrow(new \Exception("Stock decrement failed"));

        $this->machineRepository->shouldNotReceive('updateStatus');

        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });

        $response = $this->controller->selectProduct($request, $machineId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(['error' => 'An unexpected error occurred. Please try again.'], $response->getData(true));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
