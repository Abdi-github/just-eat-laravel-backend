<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = json_decode(file_get_contents(database_path('../data/permissions.json')), true);
        $roles       = json_decode(file_get_contents(database_path('../data/roles.json')), true);
        $rolePerms   = json_decode(file_get_contents(database_path('../data/role_permissions.json')), true);

        // Create permissions: resource.action  e.g. "restaurants.create"
        $permMap = []; // _id → Permission model
        foreach ($permissions as $perm) {
            $name = $perm['resource'] . '.' . $perm['action'];
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            // Also create for api guard
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
            $permMap[$perm['_id']] = $permission;
        }

        // Create roles
        $roleMap = []; // _id → Role model
        foreach ($roles as $role) {
            $r = Role::firstOrCreate(['name' => $role['name'], 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => $role['name'], 'guard_name' => 'api']);
            $roleMap[$role['_id']] = $r;
        }

        // Assign permissions to roles
        foreach ($rolePerms as $rp) {
            $role = $roleMap[$rp['role_id']] ?? null;
            $perm = $permMap[$rp['permission_id']] ?? null;
            if ($role && $perm) {
                $role->givePermissionTo($perm);
            }
        }
    }
}
