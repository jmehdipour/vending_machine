<?php

namespace Tests\Feature;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Machine;
use App\Enums\MachineStatus;

class InsertCoinTest extends TestCase
{
    use DatabaseMigrations;

    public function testInsertCoin_WhenMachineIsIdle_UpdatesStatusToProcessing()
    {
        $machine = Machine::factory()->create(['status' => MachineStatus::IDLE]);

        $response = $this->post("/api/machines/{$machine->id}/insert-coin");

        $response->seeStatusCode(200)
            ->seeJson(['message' => 'Coin inserted, you can select a product.']);

        $this->seeInDatabase('machines', [
            'id' => $machine->id,
            'status' => MachineStatus::PROCESSING
        ]);
    }

    public function testInsertCoin_WhenMachineIsBusy_ReturnsErrorMessage()
    {
        $machine = Machine::factory()->create(['status' => MachineStatus::PROCESSING]);

        $response = $this->post("/api/machines/{$machine->id}/insert-coin");

        $response->seeStatusCode(400)
            ->seeJson(['error' => 'Machine is busy']);

        $this->seeInDatabase('machines', [
            'id' => $machine->id,
            'status' => MachineStatus::PROCESSING
        ]);
    }

    public function testInsertCoin_WhenMachineDoesNotExist_ReturnsNotFoundError()
    {
        $response = $this->post("/api/machines/999/insert-coin");

        $response->seeStatusCode(404)
            ->seeJson(['error' => 'Machine not found']);
    }
}
