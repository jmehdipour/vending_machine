<?php


namespace App\Http\Controllers;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\MachineRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Enums\MachineStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller as BaseController;

class ProductController extends BaseController
{
    protected ProductRepositoryInterface $productRepository;
    protected MachineRepositoryInterface $machineRepository;
    protected TransactionRepositoryInterface $transactionRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        MachineRepositoryInterface $machineRepository,
        TransactionRepositoryInterface $transactionRepository
    )
    {
        $this->productRepository = $productRepository;
        $this->machineRepository = $machineRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function getMachineProducts($machineId): JsonResponse
    {
        $products = $this->productRepository->getProductsByMachine((int)$machineId);

        $filteredProducts = $products->map(function ($machineProduct) {
            return [
                'id' => $machineProduct->id,
                'machine_id' => $machineProduct->machine_id,
                'product_id' => $machineProduct->product_id,
                'stock' => $machineProduct->stock,
                'name' => $machineProduct->product->name
            ];
        });

        return response()->json($filteredProducts);
    }

    public function selectProduct(Request $request, int $machineId): JsonResponse
    {
        $productId = $request->input('product_id');

        if (!$productId) {
            return response()->json(['error' => 'Product ID is required'], 400);
        }

        $machine = $this->machineRepository->findById($machineId);
        if (!$machine) {
            return response()->json(['error' => 'Machine not found'], 404);
        }

        if ($machine->status !== MachineStatus::PROCESSING->value) {
            return response()->json(['error' => 'Please insert a coin first'], 400);
        }

        $productStock = $this->productRepository->getProductStockInMachine($machineId, $productId);
        if ($productStock < 1) {
            return response()->json(['error' => 'Product out of stock'], 400);
        }

        try {
            return DB::transaction(function () use ($productId, $machineId) {
                $this->productRepository->decrementProductStock($machineId, $productId);
                $this->machineRepository->updateStatus($machineId, MachineStatus::IDLE->value);
                $this->transactionRepository->createTransaction($machineId, $productId);

                return response()->json(['message' => 'Product dispensed successfully']);
            });
        } catch (\Throwable $e) {
            Log::error('Transaction failed in selectProduct', [
                'machine_id' => $machineId,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }
}
