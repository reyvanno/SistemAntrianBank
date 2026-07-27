<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::upsert([
            [
                'name' => 'admin',
                'description' => 'Administrator',
            ],
            [
                'name' => 'teller',
                'description' => 'Teller',
            ],
            [
                'name' => 'customer_service',
                'description' => 'Customer Service',
            ],
        ], ['name'], ['description']);
    }
}
