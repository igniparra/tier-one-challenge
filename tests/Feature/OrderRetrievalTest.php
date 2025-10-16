<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Order retrieval testing (GET /api/orders/{id} y /api/clients/{client}/orders).
 */
class OrderRetrievalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_order_by_id_with_items_and_client(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        Sanctum::actingAs($user);

        $client = Client::factory()->create();
        $order = Order::factory()->for($client)->create();

        OrderItem::factory()->for($order)->create(['quantity' => 2, 'unit_price' => 100, 'line_total' => 200, 'name' => 'Producto A']);
        OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 50, 'line_total' => 50, 'name' => 'Producto B']);
        $order->update(['total_amount' => 250]);

        $resp = $this->getJson("/api/orders/{$order->id}");

        $resp->assertOk()
            ->assertJsonPath('order.id', $order->id)
            ->assertJsonPath('order.client_id', $client->id)
            ->assertJsonPath('order.total_amount', 250)
            ->assertJsonCount(2, 'order.items');
    }

    /** @test */
    public function list_client_orders_with_pagination(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        Sanctum::actingAs($user);

        $client = Client::factory()->create();
        Order::factory()->for($client)->count(3)->create();

        $resp = $this->getJson("/api/clients/{$client->id}/orders?per_page=2");

        $resp->assertOk()
            ->assertJsonStructure([
                'data',
                'current_page',
                'last_page',
                'links', 
                'per_page',
                'total',
            ])
            ->assertJsonCount(2, 'data');
    }
}
