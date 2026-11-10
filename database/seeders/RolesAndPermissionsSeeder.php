<?php

namespace Database\Seeders;

use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Safe to run any time (php artisan db:seed --class=RolesAndPermissionsSeeder):
 * - creates any missing permissions
 * - creates the built-in roles if missing
 * - gives default permissions only to roles that have none yet,
 *   so changes made on the Roles & Permissions screen are kept
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (Permissions::defaultRoleGrants() as $roleName => $perms) {
            $role = Role::findOrCreate($roleName, 'web');

            if ($roleName === Permissions::SUPER_ADMIN) {
                // super admin always gets every permission
                $role->syncPermissions(Permissions::all());
                continue;
            }

            if ($role->permissions()->count() === 0) {
                $role->syncPermissions($perms);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
