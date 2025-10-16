<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $qty = $this->faker->numberBetween(1, 5);
        $unit = $this->faker->randomFloat(2, 10, 500);
        return [
            'order_id' => Order::factory(),
            'name' => $this->faker->words(2, true),
            'quantity' => $qty,
            'unit_price' => $unit,
            'line_total' => $qty * $unit,
        ];
    }
}
