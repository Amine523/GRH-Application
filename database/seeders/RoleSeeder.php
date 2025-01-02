<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $approveLeavePermission = Permission::firstOrCreate([
            'name' => 'approve leave',
            'guard_name' => 'web',
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $adminRole->givePermissionTo($approveLeavePermission);

        Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'project_manager',
            'guard_name' => 'web',
        ]);

    }
}
