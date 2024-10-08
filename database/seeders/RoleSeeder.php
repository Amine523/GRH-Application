<?php

namespace Database\Seeders;

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
        // Create the 'approve leave' permission if it doesn't exist
        $approveLeavePermission = Permission::firstOrCreate([
            'name' => 'approve leave',
            'guard_name' => 'web',
        ]);

        // Create the 'admin' role if it doesn't exist
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        // Assign 'approve leave' permission to 'admin' role
        $adminRole->givePermissionTo($approveLeavePermission);

        // Create the 'user' role if it doesn't exist
        Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        // Create the 'project_manager' role if it doesn't exist
        Role::firstOrCreate([
            'name' => 'project_manager',
            'guard_name' => 'web',
        ]);

        // Optionally, you can create more permissions and assign them to the relevant roles here
    }
}
