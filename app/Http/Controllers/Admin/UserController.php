<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function index()
    {


        $users = User::select('id', 'name', 'email', 'type', 'updated_at', 'image_path')
            ->whereIn('type', ['Super Admin', 'Admin', 'Tour Assistant', 'Staff'])
            ->latest()
            ->paginate(10);



        return view('admin.users', compact('users'));
    }



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:6|confirmed',
            'type'       => 'required|string',
            'image_path' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'email', 'type']);
        $data['password'] = Hash::make($request->password);

        // Handle profile image
        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');
            $filename = time() . '_' . Str::slug($data['name']) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/users'), $filename);
            $data['image_path'] = 'uploads/users/' . $filename;
        }

        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully!',
            'user'    => $user,
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->type === 'superadmin') {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete a super admin.'
            ]);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully!'
        ]);
    }
}
