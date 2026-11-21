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
 * - permissions added later (new modules) are given to the roles that have
 *   them in their defaults, the first time they are created
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $existing = Permission::where('guard_name', 'web')->pluck('name')->all();
        $newPermissions = array_values(array_diff(Permissions::all(), $existing));

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
            } elseif ($newPermissions) {
                $role->givePermissionTo(array_values(array_intersect($perms, $newPermissions)));
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
