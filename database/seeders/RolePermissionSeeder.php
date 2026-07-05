<?php

namespace Database\Seeders;

use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    private const MODULES = [
        'jurisprudence',
        'presidential',
        'proclamation',
        'republic',
        'execord',
        'ao',
        'mo',
        'mc',
        'genor',
        'users',
        'logs',
    ];

    public function run(): void
    {
        foreach (self::MODULES as $module) {
            RolePermission::firstOrCreate(
                ['role' => 'admin', 'module' => $module],
                ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
            );

            RolePermission::firstOrCreate(
                ['role' => 'user', 'module' => $module],
                ['can_view' => true, 'can_create' => false, 'can_update' => false, 'can_delete' => false]
            );
        }
    }
}
