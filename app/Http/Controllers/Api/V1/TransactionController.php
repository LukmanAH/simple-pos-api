<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionsRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\TransactionResource;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TransactionsRequest $request)
    {
        $transactions = Transaction::with(['customer', 'items.product'])
            ->search($request['search'])
            ->latest()
            ->paginate($request['limit'] ?? 10);

        return ApiResponse::succes(
            new PaginatedResource($transactions, TransactionResource::class),
            'Transactions'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        DB::beginTransaction();

        try {
            // Generate a unique transaction code
            $code = 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

            $productIds = collect($request->items)->pluck('product_id')->toArray();
            
            // Lock products for update to avoid race conditions on stock check
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product with ID {$item['product_id']} not found.");
                }

                // Check stock availability
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stock for product '{$product->name}' is insufficient. Available: {$product->stock}, Requested: {$item['quantity']}.");
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                ];
            }

            // Calculate tax (11% standard) and total
            $tax = $subtotal * 0.11;
            $total = $subtotal + $tax;

            // Create the parent transaction
            $transaction = Transaction::create([
                'code' => $code,
                'customer_id' => $request->customer_id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            // Save items and deduct product stock
            foreach ($itemsData as $itemData) {
                $transaction->items()->create($itemData);

                $product = $products->get($itemData['product_id']);
                $product->decrement('stock', $itemData['quantity']);
            }

            DB::commit();

            // Eager-load relations for the response
            $transaction->load(['customer', 'items.product']);

            return ApiResponse::succes(
                new TransactionResource($transaction),
                'Transaction Created Successfully',
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error(
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::with(['customer', 'items.product'])->find($id);

        if (!$transaction) {
            return ApiResponse::error('Transaction Not Found', Response::HTTP_NOT_FOUND);
        }

        return ApiResponse::succes(
            new TransactionResource($transaction),
            'Transaction'
        );
    }
}
