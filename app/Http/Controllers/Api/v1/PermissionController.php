<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public const MODULES = [
        'jurisprudence',
        'presidential',
        'proclamation',
        'republic',
        'execord',
        'ao',
        'mo',
        'mc',
        'genor',
        'users',
        'logs',
    ];

    public function index()
    {
        $permissions = RolePermission::all()->groupBy('role');

        $roles = User::distinct()->pluck('role')
            ->merge(RolePermission::distinct()->pluck('role'))
            ->unique()
            ->values()
            ->toArray();

        return response()->json([
            'roles' => $roles,
            'modules' => self::MODULES,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|string|max:255',
            'module' => ['required', 'string', Rule::in(self::MODULES)],
            'can_view' => 'required|boolean',
            'can_create' => 'required|boolean',
            'can_update' => 'required|boolean',
            'can_delete' => 'required|boolean',
        ]);

        $permission = RolePermission::updateOrCreate(
            ['role' => $validated['role'], 'module' => $validated['module']],
            [
                'can_view' => $validated['can_view'],
                'can_create' => $validated['can_create'],
                'can_update' => $validated['can_update'],
                'can_delete' => $validated['can_delete'],
            ]
        );

        return response()->json([
            'message' => 'Permission updated successfully',
            'permission' => $permission,
        ]);
    }

    public function addRole(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|string|max:255|unique:role_permissions,role',
        ]);

        $role = $validated['role'];

        foreach (self::MODULES as $module) {
            RolePermission::create([
                'role' => $role,
                'module' => $module,
                'can_view' => false,
                'can_create' => false,
                'can_update' => false,
                'can_delete' => false,
            ]);
        }

        return response()->json([
            'message' => "Role '{$role}' created successfully",
        ]);
    }
}
