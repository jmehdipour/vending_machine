<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

class MachineFactory extends Factory
{
    protected $model = Machine::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'location' => $this->faker->address,
            'status' => 'idle',
        ];
    }
}
