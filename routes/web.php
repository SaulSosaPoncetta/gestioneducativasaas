<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function (){
    return view('login');
});

Route::get('/home', function () {
    return view('homeSec');
});

Route::get('/materiaUP', function () {
    return view('materiasUpload');
});

Route::get('/alumnoUP', function () {
    return view('estMatUpload');
});

Route::get('/docenteUP', function () {
    return view('docMatUpload');
});

Route::get('/buscarAlumno', function () {
    return view('alumnosSec');
});

Route::get('/cursadaSec', function () {
    return view('cursadaSec'); 
});

Route::get('/cursadaFilt', function () {
    return view('cursadaFilt'); 
});

Route::get('/cargarNota', function () {
    return view('agregarMat'); 
});

Route::get('/ingresarAlumno', function () {
    return view('ingresarAlumno'); 
});

Route::get('/modificarAlumno', function () {
    return view('modificarAlumno'); 
});

#Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
