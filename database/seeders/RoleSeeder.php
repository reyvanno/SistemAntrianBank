<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        Role::upsert(
            [
                [
                    'name' => 'admin',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'teller',
                    'guard_name' => 'web',
                ],
                [
                    'name' => 'customer_service',
                    'guard_name' => 'web',
                ],
            ],
            ['name', 'guard_name'],
            []
        );

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}