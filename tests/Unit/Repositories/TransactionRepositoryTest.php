<?php

namespace Tests\Unit\Repositories;

use App\Models\Machine;
use App\Models\Product;
use App\Repositories\TransactionRepository;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;

class TransactionRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    protected $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new TransactionRepository();
    }

    public function testCreateTransaction_WhenValidDataProvided_CreatesTransactionSuccessfully()
    {
        $machine = Machine::factory()->create();
        $product = Product::factory()->create();

        $transaction = $this->repository->createTransaction($machine->id, $product->id);

        $this->assertNotNull($transaction);
        $this->assertEquals($machine->id, $transaction->machine_id);
        $this->assertEquals($product->id, $transaction->product_id);
        $this->assertNotNull($transaction->created_at);
        $this->assertNotNull($transaction->updated_at);
        $this->seeInDatabase('transactions', [
            'machine_id' => $machine->id,
            'product_id' => $product->id,
        ]);
    }
}
