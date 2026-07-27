<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Counter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $tellerRole = Role::where('name', 'teller')->firstOrFail();
        $customerServiceRole = Role::where('name', 'customer_service')->firstOrFail();

        $tellerCounter = Counter::where('code', 'T1')->firstOrFail();
        $customerServiceCounter = Counter::where('code', 'CS1')->firstOrFail();

        User::upsert(
            [
                [
                    'role_id' => $adminRole->id,
                    'name' => 'Administrator',
                    'email' => 'admin@sistemantrianbank.test',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ],
                [
                    'role_id' => $tellerRole->id,
                    'name' => 'Teller 1',
                    'email' => 'teller@sistemantrianbank.test',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ],
                [
                    'role_id' => $customerServiceRole->id,
                    'name' => 'Customer Service 1',
                    'email' => 'cs@sistemantrianbank.test',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ],
            ],
            ['email'],
            [
                'role_id',
                'name',
                'password',
                'is_active',
            ]
        );
    }
}