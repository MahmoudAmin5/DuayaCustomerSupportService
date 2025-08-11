<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(10)->create([
            'role' => 'agent',
        ]);
    }
}
