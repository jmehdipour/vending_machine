<?php

namespace App\Repositories\Contracts;

interface TransactionRepositoryInterface
{
    public function createTransaction(int $machineId, int $productId);
}
