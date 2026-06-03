<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Company, RolePermission, Permission};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UserController extends Controller
{
  public function index(Request $request)
  {
    $users     = User::with('company')->get();
    $companies = Company::active()->get();
    $roles     = ['admin', 'manager', 'user'];

    // Get role permissions
    $rolePermissions = RolePermission::all()->keyBy('role');

    // Get all available permissions
    $availablePermissions = Permission::all()->groupBy('module');

    return view('Admin.users', compact('users', 'companies', 'roles', 'rolePermissions', 'availablePermissions'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name'       => 'required|string|max:255',
      'email'      => 'required|string|email|max:255|unique:users',
      'password'   => 'required|string|min:8|confirmed',
      'role'       => 'required|in:admin,manager,user,ca',
      'company_id' => 'nullable|exists:companies,id',
      'status'     => 'required|in:active,inactive'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'errors'  => $validator->errors()
      ], 422);
    }

    $user = User::create([
      'name'        => $request->name,
      'email'       => $request->email,
      'password'    => Hash::make($request->password),
      'role'        => $request->role,
      'company_id'  => $request->company_id,
      'status'      => $request->status,
      'permissions' => $request->permissions ? json_decode($request->permissions, true) : []
    ]);

    return response()->json([
      'success' => true,
      'message' => 'User created successfully!',
      'user'    => $user
    ]);
  }
  public function edit($id)
  {
    try {
      $user = User::findOrFail($id);

      return response()->json($user);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'User not found'
      ], 404);
    }
  }
  public function update(Request $request, $id)
  {
    $user = User::findOrFail($id);

    $validator = Validator::make($request->all(), [
      'name'       => 'required|string|max:255',
      'email'      => 'required|string|email|max:255|unique:users,email,' . $id,
      'role'       => 'required|in:admin,manager,user',
      'company_id' => 'nullable|exists:companies,id',
      'status'     => 'required|in:active,inactive'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'errors'  => $validator->errors()
      ], 422);
    }

    $data = [
      'name'        => $request->name,
      'email'       => $request->email,
      'role'        => $request->role,
      'company_id'  => $request->company_id,
      'status'      => $request->status,
      'permissions' => $request->permissions ? json_decode($request->permissions, true) : []
    ];

    // Update password if provided
    if ($request->filled('password')) {
      $data['password'] = Hash::make($request->password);
    }

    $user->update($data);

    return response()->json([
      'success' => true,
      'message' => 'User updated successfully!',
      'user'    => $user
    ]);
  }

  public function destroy($id)
  {
    $user = User::findOrFail($id);

    // Prevent deleting yourself
    if ($user->id === auth()->id()) {
      return response()->json([
        'success' => false,
        'message' => 'You cannot delete your own account!'
      ], 403);
    }

    $user->delete();

    return response()->json([
      'success' => true,
      'message' => 'User deleted successfully!'
    ]);
  }

  public function saveRolePermissions(Request $request)
  {
    $request->validate([
      'role'        => 'required|in:admin,manager,user',
      'permissions' => 'required|array'
    ]);

    $rolePermission = RolePermission::updateOrCreate(
      ['role' => $request->role],
      ['permissions' => $request->permissions]
    );

    return response()->json([
      'success' => true,
      'message' => 'Permissions saved successfully!'
    ]);
  }

  public function getUserPermissions($role)
  {
    $permission = RolePermission::where('role', $role)->first();

    return response()->json([
      'success'     => true,
      'permissions' => $permission ? $permission->permissions : []
    ]);
  }
}
