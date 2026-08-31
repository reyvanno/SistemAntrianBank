<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $permissions = [

            // Dashboard
            'dashboard.view',

            // User
            'user.view',
            'user.create',
            'user.update',
            'user.delete',

            // Service
            'service.view',
            'service.create',
            'service.update',
            'service.delete',

            // Counter
            'counter.view',
            'counter.create',
            'counter.update',
            'counter.delete',

            // Queue
            'queue.view',
            'queue.create',
            'queue.call',
            'queue.recall',
            'queue.start',
            'queue.finish',
            'queue.skip',
            'queue.cancel',

            // Monitor
            'monitor.view',

            // Report
            'report.view',
            'report.export',

            // Role
            'role.view',
            'role.create',
            'role.update',
            'role.delete',

            // Permission
            'permission.view',
            'permission.create',
            'permission.update',
            'permission.delete',
        ];

        $data = collect($permissions)
            ->map(fn(string $permission) => [
                'name' => $permission,
                'guard_name' => 'web',
            ])
            ->toArray();

        Permission::upsert(
            $data,
            ['name', 'guard_name'],
            []
        );

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}