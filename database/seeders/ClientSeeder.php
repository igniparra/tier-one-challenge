<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

/**
 * Creates 2 clients (tennants)
 */
class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::query()->firstOrCreate(
            ['email' => 'logistics@acme.com'],
            ['name' => 'ACME Logistics']
        );

        Client::query()->firstOrCreate(
            ['email' => 'customer@example.com'],
            ['name' => 'Example Corp.']
        );
    }
}
