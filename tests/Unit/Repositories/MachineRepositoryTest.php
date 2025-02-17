<?php

namespace Tests\Unit\Repositories;

use App\Enums\MachineStatus;
use App\Models\Machine;
use App\Repositories\MachineRepository;
use Laravel\Lumen\Testing\DatabaseMigrations;
use \Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class MachineRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    protected $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new MachineRepository();
    }

    public function testFindAll_WhenMachinesExist_ReturnsAllMachines()
    {
        Machine::factory()->count(3)->create();

        $machines = $this->repository->findAll();

        $this->assertCount(3, $machines);
    }

    public function testFindById_WhenMachineExists_ReturnsMachine()
    {
        $machine = Machine::factory()->create();

        $foundMachine = $this->repository->findById($machine->id);

        $this->assertNotNull($foundMachine);
        $this->assertEquals($machine->id, $foundMachine->id);
    }

    public function testFindById_WhenMachineDoesNotExist_ReturnsNull()
    {
        $foundMachine = $this->repository->findById(999);

        $this->assertNull($foundMachine);
    }

    public function testUpdateStatus_WhenMachineExists_UpdatesStatusSuccessfully()
    {
        $machine = Machine::factory()->create(['status' => MachineStatus::IDLE->value]);

        $updatedMachine = $this->repository->updateStatus($machine->id, MachineStatus::PROCESSING->value);

        $this->assertEquals(MachineStatus::PROCESSING->value, $updatedMachine->status);
        $this->seeInDatabase('machines', [
            'id' => $machine->id,
            'status' => MachineStatus::PROCESSING->value,
        ]);
    }

    public function testUpdateStatus_WhenMachineDoesNotExist_ThrowsModelNotFoundException()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->updateStatus(999, 'active');
    }
}
