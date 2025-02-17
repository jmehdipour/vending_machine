<?php

namespace App\Repositories;

use App\Repositories\Contracts\ProductRepositoryInterface;

use App\Models\MachineProduct;

class ProductRepository implements ProductRepositoryInterface
{
    public function getProductsByMachine(int $machineId)
    {
        return MachineProduct::where('machine_id', $machineId)
            ->with('product')
            ->get();
    }

    public function decrementProductStock(int $machineId, int $productId)
    {
        return MachineProduct::where('machine_id', $machineId)
            ->where('product_id', $productId)
            ->where('stock', '>', 0)
            ->decrement('stock');
    }

    public function getProductStockInMachine(int $machineId, int $productId): int
    {
        return MachineProduct::where('machine_id', $machineId)
            ->where('product_id', $productId)
            ->value('stock') ?? 0;
    }
}
