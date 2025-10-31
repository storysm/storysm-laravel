<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GuestRoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the 'act_as_guest' permission
        Permission::firstOrCreate(['name' => 'act_as_guest', 'guard_name' => 'web']);

        // Create the 'Guest' role
        $role = Role::firstOrCreate(['name' => 'Guest', 'guard_name' => 'web']);

        // Assign the 'act_as_guest' permission to the 'Guest' role
        $role->givePermissionTo('act_as_guest');
    }
}
