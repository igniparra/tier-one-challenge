<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job that simulates the invoice creation
 * Logs to the invoice log channel.
 */
class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        Log::channel('invoice')->info('Generating Invoice...');

        sleep(2); // Invoice creation time simulation.

        $order = $this->order->fresh(['items', 'client']);
        $order->update(['status' => 'invoiced']);

        $lines = $order->items->map(function ($item) {
            $qty = (int) $item->quantity;
            $name = (string) $item->name;
            $unit = number_format((float) $item->unit_price, 2, '.', '');
            $line = number_format((float) $item->line_total, 2, '.', '');
            return "{$qty}x {$name} @ {$unit} = {$line}";
        })->all();

        $itemsStr = empty($lines) ? 'no items' : implode(', ', $lines);
        $totalStr = number_format((float) $order->total_amount, 2, '.', '');

        Log::channel('invoice')->info(
            "Invoice generated for order #{$order->id} (Client ID: {$order->client_id}) | Items: {$itemsStr} | Total: {$totalStr}"
        );
    }
}
