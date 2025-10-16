<?php

namespace App\Http\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Jobs\GenerateInvoiceJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Log;

/**
 * Orders Service
 *
 * @author igniparra
 */
class OrderService
{
    public function createOrder(array $data): Order
    {
        Log::channel('invoice')->info('Request received');
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'client_id' => $data['client_id'],
                'status' => 'pending',
                'total_amount' => 0,
            ]);

            $total = 0;

            foreach ($data['items'] as $itemData) {
                $lineTotal = $itemData['quantity'] * $itemData['unit_price'];
                $total += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'name' => $itemData['name'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'line_total' => $lineTotal,
                ]);
            }

            $order->update(['total_amount' => $total]);

            GenerateInvoiceJob::dispatch($order)->afterCommit();

            return $order->load(['items', 'client']);
        });
    }

    public function getOrderById(int $orderId): Order
    {
        return Order::with(['items', 'client'])->findOrFail($orderId);
    }
    public function listOrdersByClient(int $clientId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with('items')
            ->where('client_id', $clientId)
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
