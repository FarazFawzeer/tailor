<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $roleFilter = (string)$request->get('role');

        $users = User::query()
            ->with('roles')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($roleFilter === '__none', fn($query) => $query->doesntHave('roles'))
            ->when($roleFilter && $roleFilter !== '__none', fn($query) => $query->role($roleFilter))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $roles = Role::orderBy('name')->pluck('name');

        return view('admin.users.index', compact('users', 'roles', 'q', 'roleFilter'));
    }

    public function create()
    {
        $user = new User();
        $roles = $this->assignableRoles();

        return view('admin.users.form', compact('user', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            $this->rules(null),
            $this->messages(),
            $this->attributes()
        );

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'], // "User Name" is stored in the email column
            'password' => Hash::make($data['password']),
            'image_path' => $this->storeImage($request, $data['name']),
        ]);

        $user->syncRoles([$data['role']]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'redirect' => route('admin.users.index'),
        ]);
    }

    public function edit(User $user)
    {
        if ($denied = $this->guardSuperAdminTarget($user)) {
            return redirect()->route('admin.users.index')->with('error', $denied);
        }

        $roles = $this->assignableRoles();

        return view('admin.users.form', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        if ($denied = $this->guardSuperAdminTarget($user)) {
            return response()->json(['success' => false, 'message' => $denied], 403);
        }

        $data = $request->validate(
            $this->rules($user),
            $this->messages(),
            $this->attributes()
        );

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        if ($image = $this->storeImage($request, $data['name'])) {
            $update['image_path'] = $image;
        }

        $user->update($update);

        // you can't change your own role (avoids locking yourself out)
        if ($user->id !== auth()->id()) {
            if ($user->hasRole(Permissions::SUPER_ADMIN)
                && $data['role'] !== Permissions::SUPER_ADMIN
                && $this->superAdminCount() <= 1) {
                return response()->json(['success' => false, 'message' => 'At least one Super Admin is required.'], 422);
            }

            $user->syncRoles([$data['role']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'redirect' => route('admin.users.index'),
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 422);
        }

        if ($denied = $this->guardSuperAdminTarget($user)) {
            return response()->json(['success' => false, 'message' => $denied], 403);
        }

        if ($user->hasRole(Permissions::SUPER_ADMIN) && $this->superAdminCount() <= 1) {
            return response()->json(['success' => false, 'message' => 'At least one Super Admin is required.'], 422);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }

    // Only a super admin may give out (or manage users with) the super admin role
    private function assignableRoles()
    {
        return Role::query()
            ->when(!auth()->user()->hasRole(Permissions::SUPER_ADMIN), fn($q) => $q->where('name', '!=', Permissions::SUPER_ADMIN))
            ->orderByRaw("name = ? desc", [Permissions::SUPER_ADMIN])
            ->orderBy('name')
            ->pluck('name');
    }

    private function guardSuperAdminTarget(User $user): ?string
    {
        if ($user->hasRole(Permissions::SUPER_ADMIN) && !auth()->user()->hasRole(Permissions::SUPER_ADMIN)) {
            return 'Only a Super Admin can change a Super Admin user.';
        }

        return null;
    }

    private function superAdminCount(): int
    {
        return User::role(Permissions::SUPER_ADMIN)->count();
    }

    private function rules(?User $user): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // "User Name" (stored in users.email, not a real email)
            'email' => ['required', 'string', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
            'role' => [
                // your own role can't be changed, so it isn't required on your own account
                $user && $user->id === auth()->id() ? 'nullable' : 'required',
                Rule::in($this->assignableRoles()->all()),
            ],
            'image_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ];
    }

    private function messages(): array
    {
        return [
            'email.unique' => 'This user name is already taken. Please choose a different user name.',
            'email.required' => 'The user name field is required.',
            'role.in' => 'Please select a valid role.',
        ];
    }

    private function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'user name',
            'image_path' => 'profile image',
        ];
    }

    private function storeImage(Request $request, string $name): ?string
    {
        if (!$request->hasFile('image_path')) {
            return null;
        }

        $file = $request->file('image_path');
        $filename = time() . '_' . Str::slug($name) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/users'), $filename);

        return 'uploads/users/' . $filename;
    }
}
