<?php

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface
{
    public function getProductsByMachine(int $machineId);

    public function decrementProductStock(int $machineId, int $productId);

    public function getProductStockInMachine(int $machineId, int $productId): int;
}

