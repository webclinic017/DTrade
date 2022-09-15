<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Role::all()->isEmpty()) {
            // Define Roles
            $admin = Role::create(['name' => 'admin']);

            // Define Permissions
            $viewHorizonDashboard = Permission::create(['name' => 'view horizon dashboard']);

            $admin->syncPermissions([
                $viewHorizonDashboard,
            ]);
        }
    }
}
