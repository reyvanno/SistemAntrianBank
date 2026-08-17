<?php

namespace Database\Seeders;

use App\Models\Counter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tellerCounter = Counter::where('code', 'T1')->firstOrFail();
        $customerServiceCounter = Counter::where('code', 'CS1')->firstOrFail();

        User::upsert(
            [
                [
                    'name' => 'Administrator',
                    'email' => 'admin@sistemantrianbank.test',
                    'password' => Hash::make('password'),
                    'counter_id' => null,
                    'is_active' => true,
                ],
                [
                    'name' => 'Teller 1',
                    'email' => 'teller@sistemantrianbank.test',
                    'password' => Hash::make('password'),
                    'counter_id' => $tellerCounter->id,
                    'is_active' => true,
                ],
                [
                    'name' => 'Customer Service 1',
                    'email' => 'cs@sistemantrianbank.test',
                    'password' => Hash::make('password'),
                    'counter_id' => $customerServiceCounter->id,
                    'is_active' => true,
                ],
            ],
            ['email'],
            [
                'name',
                'password',
                'counter_id',
                'is_active',
            ]
        );

        User::where('email', 'admin@sistemantrianbank.test')
            ->firstOrFail()
            ->syncRoles('admin');

        User::where('email', 'teller@sistemantrianbank.test')
            ->firstOrFail()
            ->syncRoles('teller');

        User::where('email', 'cs@sistemantrianbank.test')
            ->firstOrFail()
            ->syncRoles('customer_service');
    }
}