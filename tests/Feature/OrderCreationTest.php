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
 */
class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function crea_una_orden_con_items_y_despacha_el_job(): void
    {
        Queue::fake();

        // Sanctum Auth
        $user = User::factory()->create([
            'password' => Hash::make('secret'),
        ]);
        Sanctum::actingAs($user);

        $client = Client::factory()->create();

        $payload = [
            'client_id' => $client->id,
            'items' => [
                ['name' => 'Laptop Dell', 'quantity' => 2, 'unit_price' => 1200],
                ['name' => 'Mouse Logitech', 'quantity' => 3, 'unit_price' => 25.5],
            ],
        ];

        $resp = $this->postJson('/api/orders', $payload);

        $resp->assertCreated()
            ->assertJsonStructure([
                'message',
                'order' => ['id', 'client_id', 'status', 'total_amount', 'items' => [['id', 'name', 'quantity', 'unit_price', 'line_total']]]
            ]);

        // Expected total = 2*1200 + 3*25.5 = 2400 + 76.5 = 2476.5
        $this->assertEquals(2476.5, $resp->json('order.total_amount'));

        // Assert if Job is queued
        Queue::assertPushed(GenerateInvoiceJob::class, 1);
    }

    /** @test */
    public function valida_requerimientos_minimos(): void
    {
        Queue::fake();

        $user = User::factory()->create(['password' => Hash::make('secret')]);
        Sanctum::actingAs($user);

        $resp = $this->postJson('/api/orders', []); // sin body

        $resp->assertStatus(422)
            ->assertJsonValidationErrors(['client_id', 'items']);
    }
}
