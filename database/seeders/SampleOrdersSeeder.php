<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Order generator
 */
class SampleOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::query()->firstOrCreate(
            ['email' => 'acme@example.com'],
            ['name' => 'ACME Logistics']
        );

        // Order #1
        DB::transaction(function () use ($client) {
            $order = Order::create([
                'client_id' => $client->id,
                'status' => 'invoiced',
                'total_amount' => 0,
            ]);

            $items = [
                ['name' => 'Dell Laptop', 'quantity' => 2, 'unit_price' => 1200.00],
                ['name' => 'Logitech Mouse', 'quantity' => 3, 'unit_price' => 25.50],
            ];

            $total = 0;
            foreach ($items as $i) {
                $line = $i['quantity'] * $i['unit_price'];
                $total += $line;

                OrderItem::create([
                    'order_id' => $order->id,
                    'name' => $i['name'],
                    'quantity' => $i['quantity'],
                    'unit_price' => $i['unit_price'],
                    'line_total' => $line,
                ]);
            }

            $order->update(['total_amount' => $total]);
        });

        // Order #2
        DB::transaction(function () use ($client) {
            $order = Order::create([
                'client_id' => $client->id,
                'status' => 'invoiced',
                'total_amount' => 0,
            ]);

            $items = [
                ['name' => 'Mechanical keyboard', 'quantity' => 1, 'unit_price' => 95.99],
                ['name' => 'Headphones', 'quantity' => 2, 'unit_price' => 49.99],
            ];

            $total = 0;
            foreach ($items as $i) {
                $line = $i['quantity'] * $i['unit_price'];
                $total += $line;

                OrderItem::create([
                    'order_id' => $order->id,
                    'name' => $i['name'],
                    'quantity' => $i['quantity'],
                    'unit_price' => $i['unit_price'],
                    'line_total' => $line,
                ]);
            }

            $order->update(['total_amount' => $total]);
        });
    }
}
