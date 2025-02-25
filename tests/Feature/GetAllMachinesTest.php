<?php


namespace Tests\Feature;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Machine;

class GetAllMachinesTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetAllMachines_WhenMachinesExist_ReturnsListOfMachines()
    {
        Machine::factory()->count(3)->create();

        $response = $this->get('/api/machines');

        $response->seeStatusCode(200)
            ->seeJsonStructure([
                '*' => ['id', 'location', 'name', 'status', 'created_at', 'updated_at']
            ]);
    }

    public function testGetAllMachines_WhenNoMachinesExist_ReturnsEmptyArray()
    {
        $response = $this->get('/api/machines');

        $response->seeStatusCode(200)
            ->seeJsonEquals([]);
    }
}
