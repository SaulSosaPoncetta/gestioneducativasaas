<?php

use App\Http\Controllers\LoginPasswordReset;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\EstudianteController;      // <-- NUEVO


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

Route::get('/ingresarAlumno', function () {
    return view('Secretaria/ingresarAlumno');
})->name('ingresarAlumno');

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

Route::get('/buscarProfesor', function () {
    return view('Secretaria/profesoresSec');
})->name('profesoresSec');

Route::get('/profesorAgregar', function () {
    return view('Secretaria.profesoresAgregar');
})->name('profesoresAgregar');

Route::get('/buscarAdministrador', function () {
    return view('Secretaria.administracionSec');
})->name('administracionSec');

Route::get('/profesorPerfil', function () {
    return view('Secretaria.profesoresPerfil');
})->name('profesoresPerfil');


// =============================================================================
// AUTH
// =============================================================================

Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/login', function () {
    return view('login');
})->name('login');


// =============================================================================
// ALUMNOS  (Controller + Service)
// =============================================================================

// Lista de alumnos con filtros
Route::get('/buscarAlumno', [EstudianteController::class, 'index'])->name('alumnos.index');

// Ver perfil de un alumno
Route::get('/alumnoPerfil/{id}', [EstudianteController::class, 'show'])->name('alumnos.show');

// Actualizar alumno (AJAX - PUT)
Route::put('/alumnoPerfil/{id}', [EstudianteController::class, 'update'])->name('alumnos.update');


// =============================================================================
// ADMINISTRADORES  (Controller + Service - ya existía)
// =============================================================================

Route::apiResource('administradores', AdministradorController::class);
