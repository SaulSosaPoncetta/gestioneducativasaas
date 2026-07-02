<?php

use App\Http\Controllers\LoginPasswordReset;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocenteController;

// =============================================================================
// RUTAS ESTÁTICAS (vistas sin datos de BD)
// =============================================================================

Route::get('/homeSec', function () {
    return view('Secretaria.homeSec');
})->name('home');

Route::get('/materiaUP', function () {
    return view('Secretaria/materiasUpload');
})->name('materiaUP');

Route::get('/alumnoUP', function () {
    return view('Secretaria/estMatUpload');
})->name('alumnoUP');

Route::get('/docenteUP', function () {
    return view('Secretaria/docMatUpload');
})->name('docenteUP');

Route::get('/cursadaSec', function () {
    return view('Secretaria.cursadaSec');
})->name('cursada');

Route::get('/cargarNota', function () {
    return view('Secretaria/cargarNota');
})->name('cargarNota');

Route::get('/materiaPerfil', function () {
    return view('Secretaria/materiaPerfil');
});

Route::get('/dashboard', fn() => view('homeSec'))->name('dashboard');

Route::get('/carreraSec', function () {
    return view('Secretaria/carreraSec');
});

Route::get('/constanciasSec', function () {
    return view('Secretaria/constanciasSec');
});

Route::get('/analiticoAlumno', function () {
    return view('Secretaria/analitico');
});

Route::middleware('auth')->group(function () {
    Route::get('Secretaria/misdatos', [ProfileController::class, 'show'])->name('misdatos');
    Route::put('/Secretaria/perfil', [ProfileController::class, 'update'])->name('perfil.update');
});

// =============================================================================
// DOCENTES
// =============================================================================

Route::get('/buscarProfesor', [DocenteController::class, 'index'])
    ->name('profesoresSec');

Route::get('/profesorAgregar', [DocenteController::class, 'create'])
    ->name('profesoresAgregar');

Route::post('/profesorAgregar', [DocenteController::class, 'store'])
    ->name('profesores.store');

Route::put('/profesor/{id}', [DocenteController::class, 'update'])
    ->name('profesores.update');

Route::get('/docentes/{id}', [DocenteController::class, 'show'])
    ->name('docentes.show');

Route::get('/usuarios', function () {
    return view('Secretaria.usuarios');
});

// =============================================================================
// AUTH
// =============================================================================

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [LoginPasswordReset::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [LoginPasswordReset::class, 'sendPasswordResetToken'])->name('password.email');

// =============================================================================
// ALUMNOS
// =============================================================================

// Lista de alumnos con filtros
Route::get('/buscarAlumno', [EstudianteController::class, 'index'])
    ->name('alumnos.index');

// Formulario agregar alumno (GET muestra el form, POST guarda)
Route::get('/alumnoAgregar', [EstudianteController::class, 'create'])
    ->name('alumnoAgregar');

Route::post('/alumnoAgregar', [EstudianteController::class, 'store'])
    ->name('alumnos.store');

// Perfil de alumno (GET muestra, PUT actualiza)
Route::get('/alumnoPerfil/{id}', [EstudianteController::class, 'show'])
    ->name('alumnos.show');

Route::put('/alumnoPerfil/{id}', [EstudianteController::class, 'update'])
    ->name('alumnos.update');

// Toggle estado activo/inactivo
Route::patch('/alumnos/{id}/estado', [EstudianteController::class, 'toggleEstado'])
    ->name('alumnos.toggleEstado');

// =============================================================================
// ADMINISTRADORES
// =============================================================================

Route::get('/buscarAdministrador', [AdministradorController::class, 'index'])
    ->name('administracionSec');

Route::get('/administradorAgregar', [AdministradorController::class, 'create'])
    ->name('administradores.create');

Route::post('/administradorAgregar', [AdministradorController::class, 'store'])
    ->name('administradores.store');

Route::put('/administrador/{id}', [AdministradorController::class, 'update'])
    ->name('administradores.update');

Route::get('/administradorPerfil/{id}', [AdministradorController::class, 'show'])
    ->name('administradores.show');

Route::patch('/administradores/{id}/estado', [AdministradorController::class, 'toggleEstado']);

// =============================================================================
// PERFIL
// =============================================================================

Route::get('perfil', [ProfileController::class, 'show']);
Route::put('perfil', [ProfileController::class, 'update']);
