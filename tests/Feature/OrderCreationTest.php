<?php

namespace Tests\Feature;

use App\Jobs\GenerateInvoiceJob;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Order creation test (POST /api/orders).
 *
 * @author igniparra
 */
class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function creates_an_order_with_items_and_dispatches_the_job(): void
    {
        Queue::fake();

        // Sanctum Auth
        $user = User::factory()->create([
            'password' => Hash::make('secret'),
        ]);
        Sanctum::actingAs($user);

        // Client must belong to this user
        $client = Client::factory()->create([
            'user_id' => $user->id,
        ]);

        $payload = [
            'items' => [
                ['name' => 'Laptop Dell', 'quantity' => 2, 'unit_price' => 1200],
                ['name' => 'Mouse Logitech', 'quantity' => 3, 'unit_price' => 25.5],
            ],
        ];

        $response = $this->postJson("/api/orders/{$client->id}", $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'order' => [
                    'id',
                    'client_id',
                    'status',
                    'total_amount',
                    'items' => [
                        ['id', 'name', 'quantity', 'unit_price', 'line_total']
                    ]
                ]
            ]);

        // Expected total = 2*1200 + 3*25.5 = 2400 + 76.5 = 2476.5
        $this->assertEquals(2476.5, $response->json('order.total_amount'));

        // Assert that the Job was queued
        Queue::assertPushed(GenerateInvoiceJob::class, 1);
    }

    /** @test */
    public function prevents_creating_order_for_unauthorized_client(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        // Client belongs to a different user
        $client = Client::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $payload = [
            'items' => [
                ['name' => 'Laptop Dell', 'quantity' => 1, 'unit_price' => 1000],
            ],
        ];

        $response = $this->postJson("/api/orders/{$client->id}", $payload);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Unauthorized: You do not have access to this client.'
            ]);

        Queue::assertNothingPushed();
    }

    /** @test */
    public function validates_minimum_requirements(): void
    {
        Queue::fake();

        $user = User::factory()->create(['password' => Hash::make('secret')]);
        Sanctum::actingAs($user);

        // This will fail validation since no body is provided
        $response = $this->postJson('/api/orders/1', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }
}
