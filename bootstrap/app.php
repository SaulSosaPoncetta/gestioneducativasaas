<?php

use App\Http\Middleware\ResolverInstitucion;
use App\Http\Middleware\VerificarRol;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'rol'         => VerificarRol::class,
            'institucion' => ResolverInstitucion::class,
        ]);

        // La institución actual se define ANTES de resolver los modelos de la
        // ruta ({ciclo}, {curso}...): así el filtro por institución también
        // aplica al route model binding y no se puede abrir un registro de
        // otra institución cambiando el id en la URL.
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: ResolverInstitucion::class,
        );

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
