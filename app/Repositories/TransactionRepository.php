<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function createTransaction(int $machineId, int $productId)
    {
        return Transaction::create([
            'machine_id' => $machineId,
            'product_id' => $productId,
        ]);
    }
}
