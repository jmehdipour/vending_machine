<?php


namespace Tests\Unit\Http\Controllers;

use App\Enums\MachineStatus;
use App\Http\Controllers\MachineController;
use App\Repositories\MachineRepository;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;

class MachineControllerTest extends TestCase
{
    private $machineRepository;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->machineRepository = Mockery::mock(MachineRepository::class);
        $this->controller = new MachineController($this->machineRepository);
    }

    public function testGetAllMachines_WhenMachinesExist_ReturnsMachinesSuccessfully()
    {
        $machines = [
            (object)['id' => 1, 'status' => 'idle'],
            (object)['id' => 2, 'status' => 'processing'],
        ];
        $this->machineRepository->shouldReceive('findAll')
            ->once()
            ->andReturn($machines);

        $response = $this->controller->getAllMachines();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($machines, $response->getData());
    }

    public function testGetAllMachines_WhenNoMachinesExist_ReturnsEmptyArray()
    {
        $this->machineRepository->shouldReceive('findAll')
            ->once()
            ->andReturn([]);

        $response = $this->controller->getAllMachines();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getData());
    }

    public function testInsertCoin_WhenMachineNotFound_Returns404Error()
    {
        $machineId = 1;
        $this->machineRepository->shouldReceive('findById')
            ->with($machineId)
            ->andReturn(null);

        $response = $this->controller->insertCoin($machineId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->status());
        $this->assertEquals(['error' => 'Machine not found'], $response->getData(true));
    }

    public function testInsertCoin_WhenMachineIsProcessing_Returns400Error()
    {
        $machineId = 1;
        $this->machineRepository->shouldReceive('findById')
            ->with($machineId)
            ->andReturn((object)['status' => MachineStatus::PROCESSING->value]);

        $response = $this->controller->insertCoin($machineId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->status());
        $this->assertEquals(['error' => 'Machine is busy'], $response->getData(true));
    }

    public function testInsertCoin_WhenMachineIsIdle_UpdatesStatusAndReturnsSuccessMessage()
    {
        $machineId = 1;
        $machine = (object)['status' => MachineStatus::IDLE->value];
        $this->machineRepository->shouldReceive('findById')
            ->with($machineId)
            ->andReturn($machine);
        $this->machineRepository->shouldReceive('updateStatus')
            ->with($machineId, MachineStatus::PROCESSING->value)
            ->once();

        $response = $this->controller->insertCoin($machineId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['message' => 'Coin inserted, you can select a product.'], $response->getData(true));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
