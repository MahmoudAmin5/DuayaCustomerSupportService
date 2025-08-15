<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Agent Test',
            'email' => 'agent@example.com',
            'phone' => '123-456-7890',
            'password' => bcrypt('password'),
            'role' => 'agent',
        ]);
    }
}
