<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => __('Administrator'),
            'email' => 'admin@admin.com',
            'role_id' => Role::where('name', 'Admin')->first()->id,
        ]);

        User::factory()->count(10)->create([
            'role_id' => Role::where('name', 'Employee')->first()->id,
        ]);
    }
}
