<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacController extends Controller
{
    public function index(): Response
    {
        $roles = Role::where('guard_name', 'web')
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $r) => [
                'id'          => $r->id,
                'name'        => $r->name,
                'permissions' => $r->permissions->pluck('name')->values(),
            ]);

        $permissions = Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $p) => [
                'id'   => $p->id,
                'name' => $p->name,
            ]);

        // Group permissions by resource prefix
        $grouped = $permissions->groupBy(fn ($p) => explode('.', $p['name'])[0]);

        return Inertia::render('Rbac/Index', [
            'roles'       => $roles,
            'permissions' => $permissions,
            'grouped'     => $grouped,
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        // Mirror for api guard
        Role::firstOrCreate(['name' => $validated['name'], 'guard_name' => 'api']);

        return back()->with('success', "Role \"{$role->name}\" created.");
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $role = Role::where('guard_name', 'web')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,' . $id],
        ]);

        $oldName = $role->name;
        $role->update(['name' => $validated['name']]);

        // Mirror rename on api guard role
        Role::where('guard_name', 'api')->where('name', $oldName)
            ->update(['name' => $validated['name']]);

        return back()->with('success', "Role renamed to \"{$validated['name']}\".");
    }

    public function destroyRole(int $id): RedirectResponse
    {
        $role = Role::where('guard_name', 'web')->findOrFail($id);
        $name = $role->name;

        $role->delete();
        Role::where('guard_name', 'api')->where('name', $name)->delete();

        return back()->with('success', "Role \"{$name}\" deleted.");
    }

    public function syncPermissions(Request $request, int $roleId): RedirectResponse
    {
        $role = Role::where('guard_name', 'web')->findOrFail($roleId);

        $validated = $request->validate([
            'permissions'   => ['present', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($validated['permissions']);

        return back()->with('success', "Permissions updated for \"{$role->name}\".");
    }

    public function storePermission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', 'regex:/^[a-z_]+\.[a-z_]+$/', 'unique:permissions,name'],
        ]);

        Permission::create(['name' => $validated['name'], 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => $validated['name'], 'guard_name' => 'api']);

        return back()->with('success', "Permission \"{$validated['name']}\" created.");
    }

    public function destroyPermission(int $id): RedirectResponse
    {
        $perm = Permission::where('guard_name', 'web')->findOrFail($id);
        $name = $perm->name;

        $perm->delete();
        Permission::where('guard_name', 'api')->where('name', $name)->delete();

        return back()->with('success', "Permission \"{$name}\" deleted.");
    }
}
