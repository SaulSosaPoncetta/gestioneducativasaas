<?php

use App\Http\Controllers\AccesoController;
use App\Http\Controllers\Admin\InstitucionController;
use App\Http\Controllers\AnaliticoController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CursadaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CicloLectivoController;
use App\Http\Controllers\ComunicadoController;
use App\Http\Controllers\ConstanciaController;
use App\Http\Controllers\ConstanciaVerificacionController;
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
use App\Http\Controllers\Portal\BandejaController;
use App\Http\Controllers\Portal\ConstanciaPortalController;
use App\Http\Controllers\Portal\EstudiantePortalController;
use App\Http\Controllers\Portal\FamiliaController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\UsuarioController;
use App\Http\Middleware\RequiereInstitucion;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Acceso
|--------------------------------------------------------------------------
*/

// Verificación pública de constancias (QR). No requiere iniciar sesión.
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/verificar', [ConstanciaVerificacionController::class, 'form'])->name('constancias.verificar.form');
    Route::get('/verificar/{codigo}', [ConstanciaVerificacionController::class, 'show'])->name('constancias.verificar');
});

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

        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('/auditoria/exportar', [AuditoriaController::class, 'exportar'])->name('auditoria.exportar');

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

        // Accesos a los portales de estudiantes y familias
        Route::post('/accesos/estudiantes/{estudiante}', [AccesoController::class, 'estudiante'])->name('accesos.estudiante');
        Route::post('/accesos/tutores/{tutor}', [AccesoController::class, 'tutor'])->name('accesos.tutor');
        Route::post('/accesos/usuarios/{usuario}/restablecer', [AccesoController::class, 'restablecer'])->name('accesos.restablecer');
        Route::post('/accesos/masivo', [AccesoController::class, 'masivo'])->name('accesos.masivo');

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
    // Portal del estudiante
    // -----------------------------------------------------------------
    Route::middleware(['rol:estudiante', RequiereInstitucion::class])->prefix('mi')->group(function () {
        Route::get('/cursada', [EstudiantePortalController::class, 'inicio'])->name('estudiante.cursada');
        Route::get('/analitico', [EstudiantePortalController::class, 'analitico'])->name('estudiante.analitico');
        Route::get('/cursadas', [EstudiantePortalController::class, 'cursadas'])->name('estudiante.cursadas');
        Route::post('/cursadas', [EstudiantePortalController::class, 'inscribirCursadas'])->name('estudiante.cursadas.inscribir');
        Route::get('/finales', [EstudiantePortalController::class, 'finales'])->name('estudiante.finales');
        Route::post('/finales/{mesa}', [EstudiantePortalController::class, 'inscribirMesa'])->name('estudiante.finales.inscribir');
        Route::delete('/finales/{mesa}', [EstudiantePortalController::class, 'bajaMesa'])->name('estudiante.finales.baja');
    });

    // -----------------------------------------------------------------
    // Portal de familias
    // -----------------------------------------------------------------
    Route::middleware(['rol:tutor', RequiereInstitucion::class])->prefix('familia')->group(function () {
        Route::get('/', [FamiliaController::class, 'hijos'])->name('tutor.hijos');
        Route::get('/hijos/{estudiante}', [FamiliaController::class, 'hijo'])->name('tutor.hijo');
    });

    // -----------------------------------------------------------------
    // Portal docente
    // -----------------------------------------------------------------
    Route::middleware(['rol:docente', RequiereInstitucion::class])->group(function () {
        Route::get('/mis-cursos', [MisCursosController::class, 'index'])->name('docente.cursos');
        Route::get('/mis-cursos/{asignacion}', [MisCursosController::class, 'show'])->name('docente.cursos.show');
    });

    // -----------------------------------------------------------------
    // Comunicados: redacción (gestión + preceptoría) y bandeja de recibidos
    // -----------------------------------------------------------------
    Route::middleware(['rol:admin,directivo,secretario,preceptor', RequiereInstitucion::class])->group(function () {
        Route::resource('comunicados', ComunicadoController::class);
        Route::post('/comunicados/{comunicado}/publicar', [ComunicadoController::class, 'publicar'])->name('comunicados.publicar');
        Route::get('/comunicados/{comunicado}/adjunto', [ComunicadoController::class, 'adjunto'])->name('comunicados.adjunto');
    });

    Route::middleware(['rol:tutor,estudiante,docente', RequiereInstitucion::class])->prefix('recibidos')->group(function () {
        Route::get('/', [BandejaController::class, 'index'])->name('bandeja.index');
        Route::get('/{comunicado}', [BandejaController::class, 'show'])->name('bandeja.show');
        Route::post('/{comunicado}/confirmar', [BandejaController::class, 'confirmar'])->name('bandeja.confirmar');
        Route::get('/{comunicado}/adjunto', [BandejaController::class, 'adjunto'])->name('bandeja.adjunto');
    });

    // -----------------------------------------------------------------
    // Constancias: emisión (gestión + preceptoría) y autogestión en portales
    // -----------------------------------------------------------------
    Route::middleware(['rol:admin,directivo,secretario,preceptor', RequiereInstitucion::class])->group(function () {
        Route::get('/constancias', [ConstanciaController::class, 'index'])->name('constancias.index');
        Route::get('/constancias/emitir', [ConstanciaController::class, 'create'])->name('constancias.create');
        Route::post('/constancias', [ConstanciaController::class, 'store'])->name('constancias.store');
        Route::get('/constancias/{constancia}', [ConstanciaController::class, 'imprimir'])->name('constancias.imprimir');
        Route::patch('/constancias/{constancia}/anular', [ConstanciaController::class, 'anular'])->name('constancias.anular');
    });

    Route::middleware(['rol:tutor,estudiante', RequiereInstitucion::class])->prefix('mis-constancias')->group(function () {
        Route::get('/', [ConstanciaPortalController::class, 'index'])->name('portal.constancias.index');
        Route::post('/', [ConstanciaPortalController::class, 'store'])->name('portal.constancias.store');
        Route::get('/{constancia}', [ConstanciaPortalController::class, 'show'])->name('portal.constancias.show');
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
