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
 * Order retrieval testing (GET /api/orders/{id} and /api/clients/{client}/orders).
 *
 * @author igniparra
 */
class OrderRetrievalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function gets_order_by_id_with_items_and_client(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        Sanctum::actingAs($user);

        // Client must belong to this user
        $client = Client::factory()->create(['user_id' => $user->id]);

        $order = Order::factory()->for($client)->create();

        OrderItem::factory()->for($order)->create([
            'quantity' => 2,
            'unit_price' => 100,
            'line_total' => 200,
            'name' => 'Product A'
        ]);

        OrderItem::factory()->for($order)->create([
            'quantity' => 1,
            'unit_price' => 50,
            'line_total' => 50,
            'name' => 'Product B'
        ]);

        $order->update(['total_amount' => 250]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('order.id', $order->id)
            ->assertJsonPath('order.client_id', $client->id)
            ->assertJsonPath('order.total_amount', 250)
            ->assertJsonCount(2, 'order.items');
    }

    /** @test */
    public function prevents_access_to_orders_from_other_users_clients(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        // Client belongs to another user
        $foreignClient = Client::factory()->create(['user_id' => $otherUser->id]);
        $order = Order::factory()->for($foreignClient)->create();

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Unauthorized: You do not have access to this client.'
            ]);
    }

    /** @test */
    public function lists_client_orders_with_pagination(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        Sanctum::actingAs($user);

        // Client must belong to this user
        $client = Client::factory()->create(['user_id' => $user->id]);

        Order::factory()->for($client)->count(3)->create();

        $response = $this->getJson("/api/clients/{$client->id}/orders?per_page=2");

        $response->assertOk()
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

    /** @test */
    public function prevents_listing_orders_for_unowned_client(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        // Client belongs to another user
        $foreignClient = Client::factory()->create(['user_id' => $otherUser->id]);
        Order::factory()->for($foreignClient)->count(2)->create();

        $response = $this->getJson("/api/clients/{$foreignClient->id}/orders");

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Unauthorized: You do not have access to this client.'
            ]);
    }
}
