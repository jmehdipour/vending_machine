<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\MachineRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Enums\MachineStatus;
use Laravel\Lumen\Routing\Controller as BaseController;

class MachineController extends BaseController
{
    protected MachineRepositoryInterface $machineRepository;

    public function __construct(MachineRepositoryInterface $machineRepository)
    {
        $this->machineRepository = $machineRepository;
    }

    public function getAllMachines(): JsonResponse
    {
        $machines = $this->machineRepository->findAll();

        return response()->json($machines);
    }

    public function insertCoin(int $machineId): JsonResponse
    {
        $machine = $this->machineRepository->findById($machineId);
        if (!$machine) {
            return response()->json(['error' => 'Machine not found'], 404);
        }

        if ($machine->status != MachineStatus::IDLE->value) {
            return response()->json(['error' => 'Machine is busy'], 400);
        }

        $updated = $this->machineRepository->updateStatus($machineId, MachineStatus::PROCESSING->value);
        if ($updated) {
            return response()->json(['message' => 'Coin inserted, you can select a product.']);
        } else {
            return response()->json(['error' => 'Failed to update machine status'], 500);
        }
    }
}
