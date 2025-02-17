<?php

namespace App\Repositories\Contracts;

interface MachineRepositoryInterface
{
    public function findAll();

    public function findById($machineId);

    public function updateStatus($machineId, $status);
}
