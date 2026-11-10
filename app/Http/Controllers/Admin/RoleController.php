<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->orderByRaw("name = ? desc", [Permissions::SUPER_ADMIN])
            ->orderBy('name')
            ->get();

        $totalPermissions = count(Permissions::all());

        return view('admin.roles.index', compact('roles', 'totalPermissions'));
    }

    public function create()
    {
        $role = new Role();
        $sections = Permissions::bySection();
        $selected = [];

        return view('admin.roles.form', compact('role', 'sections', 'selected'));
    }

    public function store(Request $request)
    {
        $data = $this->validateRole($request);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'redirect' => route('admin.roles.index'),
        ]);
    }

    public function edit(Role $role)
    {
        if ($role->name === Permissions::SUPER_ADMIN) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Super Admin always has every permission and cannot be edited.');
        }

        $sections = Permissions::bySection();
        $selected = $role->permissions->pluck('name')->all();

        return view('admin.roles.form', compact('role', 'sections', 'selected'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === Permissions::SUPER_ADMIN) {
            return response()->json(['success' => false, 'message' => 'Super Admin role cannot be changed.'], 422);
        }

        $data = $this->validateRole($request, $role);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'redirect' => route('admin.roles.index'),
        ]);
    }

    public function destroy(Role $role)
    {
        if ($role->name === Permissions::SUPER_ADMIN) {
            return response()->json(['success' => false, 'message' => 'Super Admin role cannot be deleted.'], 422);
        }

        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "This role is assigned to {$usersCount} user(s). Move them to another role first.",
            ], 422);
        }

        $role->delete();

        return response()->json(['success' => true, 'message' => 'Role deleted successfully']);
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        // store role names as lower_snake_case (ex: "Shop Manager" -> shop_manager)
        $request->merge([
            'name' => strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '_', (string)$request->input('name')), '_')),
        ]);

        return $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::notIn([Permissions::SUPER_ADMIN]),
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role?->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permissions::all())],
        ], [
            'name.unique' => 'A role with this name already exists.',
            'name.not_in' => 'This role name is reserved.',
        ], [
            'name' => 'role name',
        ]);
    }
}
