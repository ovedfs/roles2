<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:permissions.index')->only('index');
        $this->middleware('can:permissions.store')->only('store');
        $this->middleware('can:permissions.show')->only('show');
        $this->middleware('can:permissions.update')->only('update');
        $this->middleware('can:permissions.destroy')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permissions = Permission::all();

        return response()->json([
            'message' => 'Listado de Permisos',
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions'
        ]);

        $permission = new Permission();
        $permission->name = $request->name;

        if($permission->save()) {
            return response()->json([
                'message' => 'Permiso agregado correctamente',
                'permission' => $permission
            ]);
        }

        return response()->json([
            'message' => 'El Permiso no pudo ser agregado',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function show(Permission $permission)
    {
        return response()->json([
            'message' => 'Detalle del Permiso',
            'permission' => $permission
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,'.$permission->id
        ]);

        $permission->name = $request->name;

        if($permission->save()) {
            return response()->json([
                'message' => 'Permiso actualizado correctamente',
                'permission' => $permission
            ]);
        }

        return response()->json([
            'message' => 'El Permiso no pudo ser actualizado',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'message' => 'Permiso eliminado correctamente',
            'permission' => Permission::all()
        ]);
    }
}
