<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $roles = [
            'Admin',
            'Employee'
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }
    }
}
