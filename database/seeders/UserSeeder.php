<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates a test user
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'test@tierone.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('secret'),
            ]
        );
    }
}
