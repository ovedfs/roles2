# Manejo de Roles y Permisos desde una API

### Configuración inicial

- Creamos un proyecto con Laravel laamado **api.roles2**
- Iniciamos un **repositorio** en local y lo vinculamos a Github
- Agregamos la **Autenticación** con Sanctum
- Instalamos y configuramos el **package Spatie Laravel Permissions**

### Esquema base

![Untitled](Manejo%20de%20Roles%20y%20Permisos%20desde%20una%20API%20a44af2387b4c498eaaf32b26784d9777/Untitled.png)

### Permissions CRUD

- Creamos el controlador **PermissionController** y las **rutas** que requiere

```php
php artisan make:controller Api/PermissionController --api --model=Permission

// Rutas
Route::apiResource('permissions', PermissionController::class);

// Controlador
public function index()
{
    $permissions = Permission::all();

    return response()->json([
        'message' => 'Listado de Permisos',
        'permissions' => $permissions
    ]);
}

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

public function show(Permission $permission)
{
    return response()->json([
        'message' => 'Detalle del Permiso',
        'permission' => $permission
    ]);
}

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

public function destroy(Permission $permission)
{
    $permission->delete();

    return response()->json([
        'message' => 'Permiso eliminado correctamente',
        'permission' => Permission::all()
    ]);
}
```

- Registramos algunos permisos desde la API
- Agregamos la restricciones de **permisos** al **constructor** del PernissionController

```php
public function __construct()
{
    $this->middleware('can:permissions.index')->only('index');
    $this->middleware('can:permissions.store')->only('store');
    $this->middleware('can:permissions.show')->only('show');
    $this->middleware('can:permissions.update')->only('update');
    $this->middleware('can:permissions.destroy')->only('destroy');
}
```

- Hacemos algunas pruebas
- Modificamos el archivo **App\Exceptions\Handler.php** para devolver respuestas en formato json cuando se generen cierto tipo de excepciones

```php
$this->renderable(function (AccessDeniedHttpException $e, $request) {
    if ($request->is('api/*')) {
        return response()->json([
            'message' => 'This action is unauthorized.'
        ], 404);
    }
});

$this->renderable(function (NotFoundHttpException $e, $request) {
    if ($request->is('api/*')) {
        return response()->json([
            'message' => 'Record not found.'
        ], 404);
    }
});

$this->renderable(function (MethodNotAllowedHttpException $e, $request) {
    if ($request->is('api/*')) {
        return response()->json([
            'message' => 'Método HTTP no permitido para este endpoint.'
        ], 404);
    }
});
```

- Hacemos un **commit**

### Roles CRUD

- Creamos el controlador **RoleController** y las **rutas** que requiere

```php
php artisan make:controller Api/RoleController --api --model=Role

// Rutas
Route::apiResource('roles', RoleController::class);

// Controlador
public function index()
{
    $roles = Role::all();

    return response()->json([
        'message' => 'Listado de Roles',
        'roles' => $roles
    ]);
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|unique:roles'
    ]);

    $role = new Role();
    $role->name = $request->name;

    if($role->save()) {
        return response()->json([
            'message' => 'Rol agregado correctamente',
            'role' => $role
        ]);
    }

    return response()->json([
        'message' => 'El Rol no pudo ser agregado',
    ]);
}

public function show(Role $role)
{
    return response()->json([
        'message' => 'Detalle del Rol',
        'role' => $role
    ]);
}

public function update(Request $request, Role $role)
{
    $request->validate([
        'name' => 'required|unique:roles,name,'.$role->id
    ]);

    $role->name = $request->name;

    if($role->save()) {
        return response()->json([
            'message' => 'Rol actualizado correctamente',
            'role' => $role
        ]);
    }

    return response()->json([
        'message' => 'El Rol no pudo ser actualizado',
    ]);
}

public function destroy(Role $role)
{
    $role->delete();

    return response()->json([
        'message' => 'Rol eliminado correctamente',
        'role' => Role::all()
    ]);
}
```

- Hacemos pruebas creando/editando/eliminando los roles: **user, editor, admin, superadmin**
- Hacemos un **commit**

### Modelo Post

Para poder jugar con los permisos, roles y usuarios vamos a crear un módulo de posts

- Creamos el modelo **Post** con su **migración** y establecemos su **relación** con el modelo User

```php
// Migración
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->foreignId('user_id')->constrained();
    $table->timestamps();
});

// Modelo Post
protected $fillable = [
    'title',
    'content',
    'user_id',
];

public function user()
{
    return $this->belongsTo(User::class);
}

// Modelo User
public function posts()
{
    return $this->hasMany(Post::class);
}
```

- Ejecutamos la migración

```php
php artisan migrate
```

- Generamos los siguientes permisos (antes comentamos las restricciones de permisos en el PermissionController):
    - posts.index
    - post.store
    - posts.show
    - posts.update
    - posts.destroy

### ASIGNACIÓN DE PERMISOS A ROLES (role_has_permissions)

- Ahora vamos a agrupar permisos en roles de la siguiente forma:

|  | user | editor | admin | superadmin |
| --- | --- | --- | --- | --- |
| posts.index | * | * | * | * |
| posts.store |  | * | * | * |
| posts.show | * | * | * | * |
| posts.update |  | * | * | * |
| posts.destroy |  | * | * | * |
| permissions.index |  |  |  | * |
| permissions.store |  |  |  | * |
| permissions.show |  |  |  | * |
| permissions.update |  |  |  | * |
| permissions.destroy |  |  |  | * |
| roles.index |  |  |  | * |
| roles.store |  |  |  | * |
| roles.show |  |  |  | * |
| roles.update |  |  |  | * |
| roles.delete |  |  |  | * |

**Permisos especiales**

| sync.role.permissions |
| --- |
| sync.user.roles |
| sync.user.permissions |
- Creamos las Rutas y el Controlador para gestionar la asignación de permisos a los roles

```php
php artisan make:controller Api/RolePermissionsController

// Rutas
Route::get('role/{role}/permissions', [RolePermissionsController::class, 'show']);
Route::post('role/{role}/permissions', [RolePermissionsController::class, 'sync']);

// Controlador
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class RolePermissionsController extends Controller
{
    public function show(Role $role)
    {
        $permissions = Permission::all();

        return response()->json([
            'role' => $role->load('permissions'),
            'permissions' => $permissions
        ]);
    }

    public function sync(Request $request, Role $role)
    {
        $permissions = Permission::all();
        $permissionsIdsArray = $permissions->pluck('id')->toArray();

        Validator::make($request->all(), [
            "permissions"   => ["sometimes", "array", Rule::in($permissionsIdsArray)],
            'permissions.*' => 'sometimes|integer|distinct',
        ])->validate();

        $role->permissions()->sync($request->permissions);

        return response()->json([
            'role' => $role->load('permissions')
        ]);
    }
}
```

- Hacemos pruebas asignando los permisos correspondientes a los roles, de acuerdo a la tabla definida anteriormente.

![Untitled](Manejo%20de%20Roles%20y%20Permisos%20desde%20una%20API%20a44af2387b4c498eaaf32b26784d9777/Untitled%201.png)

![Untitled](Manejo%20de%20Roles%20y%20Permisos%20desde%20una%20API%20a44af2387b4c498eaaf32b26784d9777/Untitled%202.png)

- Hacemos un **commit**

### ASIGNACIÓN DE ROLES A USUARIOS (model_has_roles)

- Creamos las Rutas y el Controlador para gestionar la asignación de roles a los usuarios

```php
php artisan make:controller Api/UserRolesController

// Rutas
Route::get('user/{user}/roles', [UserRolesController::class, 'show']);
Route::post('user/{user}/roles', [UserRolesController::class, 'sync']);

// Controlador
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserRolesController extends Controller
{
    public function show(User $user)
    {
        $roles = Role::all();

        return response()->json([
            'user' => $user->load('roles'),
            'roles' => $roles
        ]);
    }

    public function sync(Request $request, User $user)
    {
        $roles = Role::all();
        $rolesIdsArray = $roles->pluck('id')->toArray();

        Validator::make($request->all(), [
            "roles"   => ["sometimes", "array", Rule::in($rolesIdsArray)],
            'roles.*' => 'sometimes|integer|distinct',
        ])->validate();

        $user->roles()->sync($request->roles);

        return response()->json([
            'user' => $user->load('roles')
        ]);
    }
}
```

- Hacemos pruebas
- Hacemos un **commit**

### ASIGNACIÓN DE PERMISOS A USUARIOS (model_has_permissions)

- Creamos las Rutas y el Controlador para gestionar la asignación de permisos a los usuarios

```php
php artisan make:controller Api/UserPermissionsController

// Rutas
Route::get('user/{user}/permissions', [UserPermissionsController::class, 'show']);
Route::post('user/{user}/permissions', [UserPermissionsController::class, 'sync']);

// Controlador
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
```

### Posts CRUD

- Creamos el controlador **PostController** y las **rutas** que requiere

```php
php artisan make:controller Api/PostController --api --model=Post

// Rutas
Route::apiResource('posts', PostController::class);

// Controller
public function index()
{
    $posts = Post::all();

    return response()->json([
        'message' => 'Listado de Posts',
        'posts' => $posts
    ]);
}

public function store(Request $request)
{
    $request->validate([
        'title' => 'required|unique:posts|max:200',
        'content' => 'required'
    ]);

    $post = auth()->user()->posts()->create([
        'title' => $request->title,
        'content' => $request->content
    ]);

    return response()->json([
        'message' => 'Post agregado correctamente',
        'post' => $post
    ]);
}

public function show(Post $post)
{
    return response()->json([
        'message' => 'Detalle de un Post',
        'post' => $post
    ]);
}

public function update(Request $request, Post $post)
{
    $request->validate([
        'title' => 'required|max:200|unique:posts,title,'.$post->id,
        'content' => 'required'
    ]);

    $post->update($request->all());

    return response()->json([
        'message' => 'Post actualizado correctamente',
        'post' => $post
    ]);
}

public function destroy(Post $post)
{
    $post->delete();

    return response()->json([
        'message' => 'Post eliminado correctamente'
    ]);
}
```

- Creamos un **UserSeeder** para dar de alta 5 usuarios de prueba

```php
php artisan make:seeder UserSeeder

// UserSeeder
User::factory(5)->create();

// DatabaseSeeder
$this->call([
    UserSeeder::class
]);
```

- Hacemos pruebas
- Hacemos un **commit**

### Probar todo el sistema

1. Hacer un **rollback** de la base de datos y cargar el UserSeeder
2. Personalizamos al **primer usuario** creado (después será el **superadmin**)
3. Con ese usuario creamos los **permisos** y **roles** necesarios
4. Vinculamos los **permisos a los roles**
5. Vinculamos los **permisos especiales** al rol superadmin
6. Personalizamos los 4 usuarios restantes
7. Les damos diferentes **roles**
8. Cargamos las **restricciones de permisos** en las funciones **constructoras** de los diferentes controladores
9. Hacemos **pruebas** con cada usuario