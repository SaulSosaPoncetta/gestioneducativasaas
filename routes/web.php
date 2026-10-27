<?php

use App\Http\Controllers\Admin\InstitucionController;
use App\Http\Controllers\AnaliticoController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CursadaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CicloLectivoController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Docente\MisCursosController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\EspacioCurricularController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\MesaExamenController;
use App\Http\Controllers\MiInstitucionController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PlanEstudioController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\UsuarioController;
use App\Http\Middleware\RequiereInstitucion;
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
| RequiereInstitucion: módulos que trabajan sobre una institución (si el
| superadmin no entró a ninguna, lo manda a elegir).
| El menú muestra solo los ítems cuyas rutas existen.
*/

Route::middleware(['auth', 'institucion'])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Mis datos (todos los usuarios)
    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/password', [PerfilController::class, 'password'])->name('perfil.password');

    // -----------------------------------------------------------------
    // Superadmin MiGestión
    // -----------------------------------------------------------------
    Route::middleware('rol:superadmin')->group(function () {
        Route::post('/institucion/{institucion}/entrar', [DashboardController::class, 'seleccionarInstitucion'])
            ->name('institucion.entrar');
        Route::post('/institucion/salir', [DashboardController::class, 'salirInstitucion'])
            ->name('institucion.salir');

        Route::resource('instituciones', InstitucionController::class)
            ->parameters(['instituciones' => 'institucion'])
            ->except(['show', 'destroy']);
        Route::patch('/instituciones/{institucion}/estado', [InstitucionController::class, 'toggleEstado'])
            ->name('instituciones.estado');
    });

    // -----------------------------------------------------------------
    // Administración de la institución
    // -----------------------------------------------------------------
    Route::middleware(['rol:admin', RequiereInstitucion::class])->group(function () {
        Route::get('/mi-institucion', [MiInstitucionController::class, 'edit'])->name('institucion.edit');
        Route::put('/mi-institucion', [MiInstitucionController::class, 'update'])->name('institucion.update');
        Route::post('/mi-institucion/token', [MiInstitucionController::class, 'generarToken'])->name('institucion.token');
        Route::delete('/mi-institucion/token', [MiInstitucionController::class, 'revocarToken'])->name('institucion.token.revocar');

        Route::resource('usuarios', UsuarioController::class)
            ->parameters(['usuarios' => 'usuario'])
            ->except(['show', 'destroy']);
        Route::patch('/usuarios/{usuario}/estado', [UsuarioController::class, 'toggleEstado'])
            ->name('usuarios.estado');
    });

    // -----------------------------------------------------------------
    // Gestión académica (administrador, directivos y secretaría)
    // -----------------------------------------------------------------
    Route::middleware(['rol:admin,directivo,secretario', RequiereInstitucion::class])->group(function () {

        // Ciclos lectivos y períodos de evaluación
        Route::resource('ciclos', CicloLectivoController::class)
            ->parameters(['ciclos' => 'ciclo']);
        Route::patch('/ciclos/{ciclo}/estado', [CicloLectivoController::class, 'estado'])
            ->name('ciclos.estado');
        Route::put('/ciclos/{ciclo}/periodos', [CicloLectivoController::class, 'periodos'])
            ->name('ciclos.periodos');
        Route::post('/ciclos/{ciclo}/niveles/{nivel}/esquema', [CicloLectivoController::class, 'esquema'])
            ->name('ciclos.esquema');

        // Cursos y divisiones (niveles escolares)
        Route::resource('cursos', CursoController::class)->except(['show']);

        // Planes de estudio (todos los niveles) y sus espacios curriculares
        Route::resource('planes', PlanEstudioController::class)
            ->parameters(['planes' => 'plan']);
        Route::post('/planes/{plan}/duplicar', [PlanEstudioController::class, 'duplicar'])
            ->name('planes.duplicar');
        Route::resource('planes.espacios', EspacioCurricularController::class)
            ->parameters(['planes' => 'plan', 'espacios' => 'espacio'])
            ->only(['create', 'store', 'edit', 'update', 'destroy'])
            ->shallow();

        // Estudiantes (alta y edición del legajo)
        Route::resource('estudiantes', EstudianteController::class)
            ->parameters(['estudiantes' => 'estudiante'])
            ->only(['create', 'store', 'edit', 'update']);

        // Tutores vinculados a un estudiante
        Route::get('/estudiantes/{estudiante}/tutores/agregar', [TutorController::class, 'create'])
            ->name('estudiantes.tutores.create');
        Route::post('/estudiantes/{estudiante}/tutores', [TutorController::class, 'store'])
            ->name('estudiantes.tutores.store');
        Route::get('/estudiantes/{estudiante}/tutores/{tutor}/editar', [TutorController::class, 'editVinculo'])
            ->name('estudiantes.tutores.edit');
        Route::put('/estudiantes/{estudiante}/tutores/{tutor}', [TutorController::class, 'updateVinculo'])
            ->name('estudiantes.tutores.update');
        Route::delete('/estudiantes/{estudiante}/tutores/{tutor}', [TutorController::class, 'desvincular'])
            ->name('estudiantes.tutores.destroy');

        // Tutores (datos)
        Route::resource('tutores', TutorController::class)
            ->parameters(['tutores' => 'tutor'])
            ->only(['edit', 'update', 'destroy']);

        // Inscripciones
        Route::get('/estudiantes/{estudiante}/inscribir', [InscripcionController::class, 'create'])
            ->name('estudiantes.inscripciones.create');
        Route::post('/estudiantes/{estudiante}/inscripciones', [InscripcionController::class, 'store'])
            ->name('estudiantes.inscripciones.store');
        Route::get('/inscripciones/{inscripcion}/editar', [InscripcionController::class, 'edit'])
            ->name('inscripciones.edit');
        Route::put('/inscripciones/{inscripcion}', [InscripcionController::class, 'update'])
            ->name('inscripciones.update');
        Route::patch('/inscripciones/{inscripcion}/estado', [InscripcionController::class, 'estado'])
            ->name('inscripciones.estado');
        Route::delete('/inscripciones/{inscripcion}', [InscripcionController::class, 'destroy'])
            ->name('inscripciones.destroy');

        // Docentes y asignaciones
        Route::resource('docentes', DocenteController::class)
            ->parameters(['docentes' => 'docente'])
            ->except(['destroy']);
        Route::resource('asignaciones', AsignacionController::class)
            ->parameters(['asignaciones' => 'asignacion'])
            ->except(['show']);
    });

    // -----------------------------------------------------------------
    // Asistencia diaria (preceptoría, dirección y secretaría)
    // -----------------------------------------------------------------
    Route::middleware(['rol:admin,directivo,secretario,preceptor', RequiereInstitucion::class])->group(function () {
        Route::get('/asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
        Route::get('/asistencia/cursos/{curso}', [AsistenciaController::class, 'tomar'])->name('asistencia.tomar');
        Route::post('/asistencia/cursos/{curso}', [AsistenciaController::class, 'guardar'])->name('asistencia.guardar');
        Route::get('/asistencia/cursos/{curso}/mensual', [AsistenciaController::class, 'mensual'])->name('asistencia.mensual');
        Route::patch('/asistencia/{asistencia}/justificar', [AsistenciaController::class, 'justificar'])->name('asistencia.justificar');
    });

    // -----------------------------------------------------------------
    // Calificaciones
    // -----------------------------------------------------------------
    Route::middleware(['rol:admin,directivo,secretario', RequiereInstitucion::class])->group(function () {
        Route::get('/calificaciones/cargar', [CalificacionController::class, 'cargar'])->name('calificaciones.cargar');
        Route::post('/calificaciones/cargar', [CalificacionController::class, 'guardar'])->name('calificaciones.guardar');
        Route::delete('/calificaciones/{calificacion}', [CalificacionController::class, 'eliminar'])->name('calificaciones.eliminar');
        Route::get('/calificaciones/configuracion', [CalificacionController::class, 'configuracion'])->name('calificaciones.configuracion');
        Route::put('/calificaciones/configuracion', [CalificacionController::class, 'guardarConfiguracion'])->name('calificaciones.configuracion.guardar');
    });
    // -----------------------------------------------------------------
    // Nivel superior: cursadas, mesas de examen y analítico
    // -----------------------------------------------------------------
    Route::middleware(['rol:admin,directivo,secretario', RequiereInstitucion::class])->group(function () {
        Route::get('/cursadas', [CursadaController::class, 'index'])->name('cursadas.index');
        Route::get('/cursadas/espacios/{espacio}', [CursadaController::class, 'comision'])->name('cursadas.comision');
        Route::put('/cursadas/espacios/{espacio}', [CursadaController::class, 'cerrar'])->name('cursadas.cerrar');
        Route::get('/estudiantes/{estudiante}/cursadas', [CursadaController::class, 'inscribir'])->name('cursadas.inscribir');
        Route::post('/estudiantes/{estudiante}/cursadas', [CursadaController::class, 'guardarInscripcion'])->name('cursadas.guardar');
        Route::delete('/cursadas/{cursada}', [CursadaController::class, 'destroy'])->name('cursadas.destroy');

        Route::resource('mesas', MesaExamenController::class)->parameters(['mesas' => 'mesa']);
        Route::post('/mesas/{mesa}/inscribir', [MesaExamenController::class, 'inscribir'])->name('mesas.inscribir');
        Route::delete('/mesas/{mesa}/inscripciones/{inscripcion}', [MesaExamenController::class, 'quitar'])->name('mesas.quitar');
        Route::put('/mesas/{mesa}/acta', [MesaExamenController::class, 'acta'])->name('mesas.acta');
        Route::post('/mesas/{mesa}/cerrar', [MesaExamenController::class, 'cerrar'])->name('mesas.cerrar');
        Route::get('/mesas/{mesa}/imprimir', [MesaExamenController::class, 'imprimir'])->name('mesas.imprimir');
    });

    Route::middleware(['rol:admin,directivo,secretario,preceptor', RequiereInstitucion::class])->group(function () {
        Route::get('/estudiantes/{estudiante}/analitico', [AnaliticoController::class, 'show'])->name('analitico.show');
        Route::get('/calificaciones', [CalificacionController::class, 'index'])->name('calificaciones.index');
        Route::get('/estudiantes/{estudiante}/boletin', [CalificacionController::class, 'boletin'])->name('calificaciones.boletin');
    });

    // -----------------------------------------------------------------
    // Portal docente
    // -----------------------------------------------------------------
    Route::middleware(['rol:docente', RequiereInstitucion::class])->group(function () {
        Route::get('/mis-cursos', [MisCursosController::class, 'index'])->name('docente.cursos');
        Route::get('/mis-cursos/{asignacion}', [MisCursosController::class, 'show'])->name('docente.cursos.show');
    });

    // -----------------------------------------------------------------
    // Consulta de estudiantes y familias (gestión + preceptoría)
    // Va después del grupo anterior para que /estudiantes/crear no
    // lo tome {estudiante}.
    // -----------------------------------------------------------------
    Route::middleware(['rol:admin,directivo,secretario,preceptor', RequiereInstitucion::class])->group(function () {
        Route::get('/estudiantes', [EstudianteController::class, 'index'])->name('estudiantes.index');
        Route::get('/estudiantes/{estudiante}', [EstudianteController::class, 'show'])->name('estudiantes.show');
        Route::get('/tutores', [TutorController::class, 'index'])->name('tutores.index');
    });
});
