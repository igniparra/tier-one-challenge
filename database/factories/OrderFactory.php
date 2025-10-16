<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'status' => 'pending',
            'total_amount' => 0,
        ];
    }

    public function invoiced(): self
    {
        return $this->state(fn() => ['status' => 'invoiced']);
    }
}
