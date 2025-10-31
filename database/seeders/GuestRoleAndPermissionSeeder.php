<?php

namespace Database\Seeders;

use App\Constants\Permissions;
use App\Constants\Roles;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuestRoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create the ACT_AS_GUEST permission
            $permission = Permission::firstOrCreate(['name' => Permissions::ACT_AS_GUEST, 'guard_name' => 'web']);

            // Create the GUEST role
            $role = Role::firstOrCreate(['name' => Roles::GUEST, 'guard_name' => 'web']);

            // Assign the ACT_AS_GUEST permission to the GUEST role
            $role->givePermissionTo($permission);
        });
    }
}
