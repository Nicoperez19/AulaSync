<?php
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

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

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/user/user_index', [UserController::class, 'index'])->name('users.index');
    Route::post('/user/user_store', [UserController::class, 'store'])->name('users.add');
    Route::delete('/user/user_delete/{id}', [UserController::class, 'destroy'])->name('users.delete');
    Route::get('/user/user_edit/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/user/user_update/{id}', [UserController::class, 'update'])->name('users.update');


});

Route::group(['middleware' => ['permission:mantenedor de roles']], function () {
    Route::get('/rol/rol_index', [RoleController::class, 'index'])->name('roles.index');
    Route::delete('/rol/rol_delete/{id}', [RoleController::class, 'destroy'])->name('roles.delete');
    Route::get('/rol/rol_edit/{id}', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/rol/rol_update/{id}', [RoleController::class, 'update'])->name('roles.update');

});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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
