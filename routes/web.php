<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Acceso
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Sistema (requiere sesión)
|--------------------------------------------------------------------------
| 'institucion' define la institución actual y verifica que el usuario y la
| institución sigan habilitados. Cada módulo agrega su grupo con 'rol:...'.
| El menú lateral muestra solo los ítems cuyas rutas existen.
*/

Route::middleware(['auth', 'institucion'])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Superadmin: entrar / salir de una institución
    Route::middleware('rol:superadmin')->group(function () {
        Route::post('/institucion/{institucion}/entrar', [DashboardController::class, 'seleccionarInstitucion'])
            ->name('institucion.entrar');
        Route::post('/institucion/salir', [DashboardController::class, 'salirInstitucion'])
            ->name('institucion.salir');
    });
});
