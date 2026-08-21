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

        /*
        |--------------------------------------------------------------------------
        | Initial Admin Permissions
        |--------------------------------------------------------------------------
        |
        | Permission berikut hanya digunakan sebagai akses awal
        | untuk mengelola role dan permission melalui dashboard.
        |
        */

        $admin = Role::findByName('admin', 'web');

        $admin->givePermissionTo([
            'role.view',
            'role.create',
            'role.update',
            'role.delete',

            'permission.view',
            'permission.create',
            'permission.update',
            'permission.delete',
        ]);

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}