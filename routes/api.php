<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserRolesController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RolePermissionsController;
use App\Http\Controllers\Api\UserPermissionsController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => ["auth:sanctum"]], function(){
    Route::get('profile', [AuthController::class, 'profile']);
    Route::get('logout', [AuthController::class, 'logout']);
    
    // CRUD Permissions
    Route::apiResource('permissions', PermissionController::class);

    // CRUD Roles
    Route::apiResource('roles', RoleController::class);

    // Vincular Permisos a Roles
    Route::get('role/{role}/permissions', [RolePermissionsController::class, 'show']);
    Route::post('role/{role}/permissions', [RolePermissionsController::class, 'sync']);

    // Vincular Roles a Usuarios
    Route::get('user/{user}/roles', [UserRolesController::class, 'show']);
    Route::post('user/{user}/roles', [UserRolesController::class, 'sync']);

    // Vincular Permisos a Usuarios
    Route::get('user/{user}/permissions', [UserPermissionsController::class, 'show']);
    Route::post('user/{user}/permissions', [UserPermissionsController::class, 'sync']);

    // CRUD Posts
    Route::apiResource('posts', PostController::class);
});