<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class UserPermissionsController extends Controller
{
    public function show(User $user)
    {
        $permissions = Permission::all();

        return response()->json([
            'user' => $user->load('permissions'),
            'permissions' => $permissions
        ]);
    }

    public function sync(Request $request, User $user)
    {
        $permissions = Permission::all();
        $permissionsIdsArray = $permissions->pluck('id')->toArray();

        Validator::make($request->all(), [
            "permissions"   => ["sometimes", "array", Rule::in($permissionsIdsArray)],
            'permissions.*' => 'sometimes|integer|distinct',
        ])->validate();

        $user->permissions()->sync($request->permissions);

        return response()->json([
            'user' => $user->load('permissions')
        ]);
    }
}
