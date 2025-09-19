<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles', 'area')->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $areas = Area::where('is_active', true)->get();
        return view('admin.users.create', compact('roles', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'areas' => 'nullable|required_if:role,Field Worker|array',
            'areas.*' => 'exists:areas,id',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'nid_no' => 'nullable|string|max:20|unique:users,nid_no',
            'joining_date' => 'nullable|date',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'documents.*' => 'nullable|file|max:2048',
        ]);

        DB::transaction(function() use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'nid_no' => $request->nid_no,
                'joining_date' => $request->joining_date,
                'address' => $request->address,
            ]);

            $user->assignRole($request->role);

            if ($request->role === 'Field Worker' && $request->has('areas')) {
                $user->areas()->sync($request->areas);
            }

            if ($request->hasFile('photo')) {
                $user->addMediaFromRequest('photo')->toMediaCollection('user_photo');
            }
            if ($request->hasFile('documents')) {
                $user->addMultipleMediaFromRequest(['documents'])->toMediaCollection('user_documents');
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $areas = Area::where('is_active', true)->get();
        return view('admin.users.edit', compact('user', 'roles', 'areas'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'areas' => 'nullable|required_if:role,Field Worker|array',
            'areas.*' => 'exists:areas,id',
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'nid_no' => 'nullable|string|max:20|unique:users,nid_no,' . $user->id,
            'joining_date' => 'nullable|date',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'documents.*' => 'nullable|file|max:2048',
        ]);

        DB::transaction(function() use ($request, $user) {
            $data = $request->only(['name', 'email', 'phone', 'nid_no', 'joining_date', 'address']);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);
            $user->syncRoles($request->role);

            if ($request->role === 'Field Worker' && $request->has('areas')) {
                $user->areas()->sync($request->areas);
            } else {
                $user->areas()->sync([]); // যদি ভূমিকা পরিবর্তন করে অ্যাডমিন করা হয়, তাহলে এলাকাগুলো মুছে দিন
            }

            if ($request->hasFile('photo')) {
                $user->clearMediaCollection('user_photo');
                $user->addMediaFromRequest('photo')->toMediaCollection('user_photo');
            }
            if ($request->hasFile('documents')) {
                // আপনি চাইলে পুরানো ডকুমেন্ট রেখে নতুন যোগ করতে পারেন, অথবা পুরানো মুছে নতুন যোগ করতে পারেন
                // $user->clearMediaCollection('user_documents');
                $user->addMultipleMediaFromRequest(['documents'])->toMediaCollection('user_documents');
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // অ্যাডমিন নিজেকে ডিলিট করতে পারবে না
        if (auth()->id() == $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete yourself.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
