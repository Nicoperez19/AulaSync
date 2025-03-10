<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

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

Route::middleware('auth')->group(function () {
    Route::get('user/user_index', [UserController::class, 'index'])->name('users.index');
});

Route::middleware('auth')->group(function () {
    Route::post('user/user_store', [UserController::class, 'store'])->name('users.add');
});

Route::middleware('auth')->group(function () {
    Route::delete('user/user_delete/{id}', [UserController::class, 'destroy'])->name('users.delete');
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
