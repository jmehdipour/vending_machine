<?php

namespace App\Repositories;

use App\Models\Machine;
use App\Repositories\Contracts\MachineRepositoryInterface;

class MachineRepository implements MachineRepositoryInterface
{
    public function findAll()
    {
        return Machine::all();
    }

    public function findById($machineId)
    {
        return Machine::find($machineId);
    }

    public function updateStatus($machineId, $status)
    {
        return Machine::where('id', $machineId)->update(['status' => $status]);
    }
}
