<?php

namespace Database\Seeders;

use App\Models\Counter;
use App\Models\Service;
use Illuminate\Database\Seeder;

class CounterSeeder extends Seeder
{
    public function run(): void
    {
        $teller = Service::where('code', 'A')->firstOrFail();
        $customerService = Service::where('code', 'B')->firstOrFail();

        Counter::upsert(
            [
                [
                    'service_id' => $teller->id,
                    'code' => 'T1',
                    'name' => 'Teller 1',
                    'is_active' => true,
                ],
                [
                    'service_id' => $teller->id,
                    'code' => 'T2',
                    'name' => 'Teller 2',
                    'is_active' => true,
                ],
                [
                    'service_id' => $teller->id,
                    'code' => 'T3',
                    'name' => 'Teller 3',
                    'is_active' => true,
                ],
                [
                    'service_id' => $customerService->id,
                    'code' => 'CS1',
                    'name' => 'Customer Service 1',
                    'is_active' => true,
                ],
                [
                    'service_id' => $customerService->id,
                    'code' => 'CS2',
                    'name' => 'Customer Service 2',
                    'is_active' => true,
                ],
            ],  
            ['code'],
            [
                'service_id',
                'name',
                'is_active',
            ]
        );
    }
}