<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StaffProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    private function staffRoles(): array
    {
        // Only staff roles (not admin/super_admin)
        return ['front_desk', 'cutter', 'sewing', 'button', 'ironing', 'packaging'];
    }

    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));

        $staff = User::query()
            ->whereHas('roles', function ($r) {
                $r->whereIn('name', $this->staffRoles());
            })
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->with(['roles', 'staffProfile'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('staff.index', compact('staff', 'q'));
    }

    public function create()
    {
        $roles = $this->staffRoles();
        return view('staff.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],

            // This is "User Name" but saved in users.email column
            'email'    => ['required', 'string', 'max:255', 'unique:users,email'],

            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role'     => ['required', 'in:' . implode(',', $this->staffRoles())],

            'phone'    => ['nullable', 'string', 'max:50'],
            'nic'      => ['nullable', 'string', 'max:50'],
            'address'  => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'], // stores username text in email column
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        StaffProfile::create([
            'user_id' => $user->id,
            'phone' => $data['phone'] ?? null,
            'nic' => $data['nic'] ?? null,
            'address' => $data['address'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Staff created successfully',
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email, // contains username text
                    'role' => $data['role'],
                ]
            ]);
        }

        return redirect()->route('staff.index')->with('success', 'Staff created');
    }

    public function edit(User $staff)
    {
        $roles = $this->staffRoles();
        $staff->load(['roles', 'staffProfile']);

        return view('staff.edit', compact('staff', 'roles'));
    }

    public function update(Request $request, User $staff)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,' . $staff->id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role'     => ['required', 'in:' . implode(',', $this->staffRoles())],

            'phone'    => ['nullable', 'string', 'max:50'],
            'nic'      => ['nullable', 'string', 'max:50'],
            'address'  => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $staff->update($update);

        $staff->syncRoles([$data['role']]);

        $profile = $staff->staffProfile ?: new StaffProfile(['user_id' => $staff->id]);
        $profile->fill([
            'phone' => $data['phone'] ?? null,
            'nic' => $data['nic'] ?? null,
            'address' => $data['address'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);
        $profile->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Staff updated successfully',
            ]);
        }

        return redirect()->route('staff.index')->with('success', 'Staff updated');
    }

    public function destroy(Request $request, User $staff)
    {
        $staff->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Staff deleted successfully',
            ]);
        }

        return redirect()->route('staff.index')->with('success', 'Staff deleted');
    }
}
