<?php
use App\Http\Controllers\AreaAcademicaController;
use App\Http\Controllers\EspacioController;
use App\Http\Controllers\FacultadController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\MapasController;
use App\Http\Controllers\ReservasController;
use App\Http\Controllers\PermisionController;
use App\Http\Controllers\PisoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UniversidadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AsignaturaController;


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use Spatie\Permission\Middleware\RoleMiddleware;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});

Route::get('dashboard', function () {
    return view('layouts/dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');//el ultimo es el nombre de la ruta simplemente.

Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('/user/user_index', [UserController::class, 'index'])->name('users.index');
    Route::post('/user/user_store', [UserController::class, 'store'])->name('users.add');
    Route::delete('/user/user_delete/{run}', [UserController::class, 'destroy'])->name('users.delete');
    Route::get('/user/user_edit/{run}', [UserController::class, 'edit'])->name('users.edit');
    Route::put('user/user_update/{run}', [UserController::class, 'update'])->name('users.update');
});

Route::group(['middleware' => ['permission:mantenedor de roles']], function () {
    Route::get('/rol/rol_index', [RoleController::class, 'index'])->name('roles.index');
    Route::delete('/rol/rol_delete/{id}', [RoleController::class, 'destroy'])->name('roles.delete');
    Route::get('/rol/rol_edit/{id}', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/rol/rol_update/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::post('/rol/rol_store', [RoleController::class, 'store'])->name('roles.add');
});

Route::group(['middleware' => ['permission:mantenedor de permisos']], function () {
    Route::get('/permission/permission_index', [PermisionController::class, 'index'])->name('permissions.index');
    Route::delete('/permission/permission_delete/{id}', [PermisionController::class, 'destroy'])->name('permission.delete');
    Route::get('/permission/permission_edit/{id}', [PermisionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permission/permission_update/{id}', [PermisionController::class, 'update'])->name('permissions.update');
    Route::post('/permission/permission_store', [PermisionController::class, 'store'])->name('permission.add');
});

Route::group(['middleware' => ['permission:mantenedor de universidades']], function () {
    Route::get('/universities', [UniversidadController::class, 'index'])->name('universities.index');
    Route::post('/universities', [UniversidadController::class, 'store'])->name('universities.store');
    Route::get('/universities/{id}/edit', [UniversidadController::class, 'edit'])->name('universities.edit');
    Route::put('/universities/{id}', [UniversidadController::class, 'update'])->name('universities.update');
    Route::delete('/universities/{id}', [UniversidadController::class, 'destroy'])->name('universities.delete');
});

Route::group(['middleware' => ['permission:mantenedor de facultades']], function () {
    Route::get('/faculties', [FacultadController::class, 'index'])->name('faculties.index');
    Route::post('/faculties', [FacultadController::class, 'store'])->name('faculties.add');
    Route::get('/faculties/{id}/edit', [FacultadController::class, 'edit'])->name('faculties.edit');
    Route::put('/faculties/{id}', [FacultadController::class, 'update'])->name('faculties.update');
    Route::delete('/faculties/{id}', [FacultadController::class, 'destroy'])->name('faculties.delete');
});

Route::group(['middleware' => ['permission:mantenedor de carreras']], function () {
    Route::get('/careers', [CarreraController::class, 'index'])->name('careers.index');
    Route::post('/careers', [CarreraController::class, 'store'])->name('careers.add');
    Route::get('/careers/{id}/edit', [CarreraController::class, 'edit'])->name('careers.edit');
    Route::put('/careers/{id}', [CarreraController::class, 'update'])->name('careers.update');
    Route::delete('/careers/{id}', [CarreraController::class, 'destroy'])->name('careers.delete');
});


Route::group(['middleware' => ['permission:mantenedor de areas academicas']], function () {
    Route::get('/academic_areas', [AreaAcademicaController::class, 'index'])->name('academic_areas.index');
    Route::post('/academic_areas', [AreaAcademicaController::class, 'store'])->name('academic_areas.add');
    Route::get('/academic_areas/{id}/edit', [AreaAcademicaController::class, 'edit'])->name('academic_areas.edit');
    Route::put('/academic_areas/{id}', [AreaAcademicaController::class, 'update'])->name('academic_areas.update');
    Route::delete('/academic_areas/{id}', [AreaAcademicaController::class, 'destroy'])->name('academic_areas.delete');
});

Route::group(['middleware' => ['permission:mantenedor de pisos']], function () {
    Route::get('/floors', [PisoController::class, 'index'])->name('floors_index');
    Route::post('/facultad/{facultadId}/agregar-piso', [PisoController::class, 'agregarPiso'])->name('floors.agregarPiso');
    Route::delete('/facultad/{facultadId}/eliminar-piso', [PisoController::class, 'eliminarPiso'])->name('floors.eliminarPiso');
});

Route::group(['middleware' => ['permission:mantenedor de espacios']], function () {
    Route::get('spaces', [EspacioController::class, 'index'])->name('spaces_index');
    Route::get('spaces/{id_espacio}/edit', [EspacioController::class, 'edit'])->name('spaces.edit');
    Route::post('/spaces', [EspacioController::class, 'store'])->name(name: 'spaces.store');
    Route::put('spaces/{id_espacio}', [EspacioController::class, 'update'])->name('spaces.update');
    Route::delete('/spaces/{id}', [EspacioController::class, 'destroy'])->name('spaces.delete');
    Route::get('/facultades/{id}', [EspacioController::class, 'getFacultades']);
    Route::get('/pisos/{id}', [EspacioController::class, 'getPisos']);
});


Route::group(['middleware' => ['permission:mantenedor de reservas']], function () {
    Route::get('/reservas', [ReservasController::class, 'index'])->name('reservas.index');
    Route::get('/reservas/create', [ReservasController::class, 'create'])->name('reservas.add');
    Route::post('/reservas', [ReservasController::class, 'store'])->name('reservas.store');
    Route::get('/reservas/{id_reserva}/edit', [ReservasController::class, 'edit'])->name('reservas.edit');
    Route::put('/reservas/{id_reserva}', [ReservasController::class, 'update'])->name('reservas.update');
    Route::delete('/reservas/{id_reserva}', [ReservasController::class, 'destroy'])->name('reservas.delete');
    Route::get('/espacios-disponibles', [ReservasController::class, 'getEspaciosDisponibles']);
});

Route::group(['middleware' => ['permission:mantenedor de asignaturas']], function () {
    Route::get('/asignaturas', [AsignaturaController::class, 'index'])->name('asignaturas.index');
    Route::post('/asignaturas', [AsignaturaController::class, 'store'])->name('asignaturas.store');
    Route::get('/{id_asignatura}/edit', [AsignaturaController::class, 'edit'])->name('asignaturas.edit');
    Route::put('/{id_asignatura}', [AsignaturaController::class, 'update'])->name('asignaturas.update');
    Route::delete('/{id_asignatura}', [AsignaturaController::class, 'destroy'])->name('asignaturas.destroy');

});

Route::group(['middleware' => ['permission:mantenedor de mapas']], function () {
    Route::get('/mapas', [MapasController::class, 'index'])->name('mapas.index');
    Route::get('/mapas/add', [MapasController::class, 'add'])->name('mapas.add');
    Route::get('/mapas/facultades/{universidad}', [MapasController::class, 'getFacultades']);
    Route::get('/mapas/pisos/{facultad}', [MapasController::class, 'getPisos']);
    Route::get('/mapas/espacios/{piso}', [MapasController::class, 'getEspacios']);
    Route::post('/mapas/store', [MapasController::class, 'store'])->name('mapas.store');
    Route::get('/mapas/contar-espacios/{pisoId}', [MapasController::class, 'contarEspacios']);


});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});


Route::get('/buttons/text', function () {
    return view('buttons-showcase.text');
})->middleware(['auth'])->name('buttons.text');

Route::get('/buttons/icon', function () {
    return view('buttons-showcase.icon');
})->middleware(['auth'])->name('buttons.icon');

Route::get('/buttons/text-icon', function () {
    return view('buttons-showcase.text-icon');
})->middleware(['auth'])->name('buttons.text-icon');

require __DIR__ . '/auth.php';
