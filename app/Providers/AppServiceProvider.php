<?php

namespace App\Providers;

use App\Models\Auditoria;
use App\Observers\AuditoriaObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Paginación con estilos de Bootstrap 5.
        Paginator::useBootstrapFive();

        // URLs en castellano para Route::resource: /usuarios/crear, /usuarios/5/editar
        Route::resourceVerbs([
            'create' => 'crear',
            'edit'   => 'editar',
        ]);

        // Auditoría: altas, modificaciones y bajas de los modelos principales.
        foreach (array_keys(Auditoria::MODELOS) as $modelo) {
            $modelo::observe(AuditoriaObserver::class);
        }

        // Auditoría: ingresos y salidas del sistema.
        Event::listen(Login::class, fn (Login $e) => Auditoria::registrar('ingreso', 'Ingresó al sistema', [
            'usuario'        => $e->user,
            'institucion_id' => $e->user->institucion_id,
        ]));

        Event::listen(Logout::class, fn (Logout $e) => $e->user ? Auditoria::registrar('salida', 'Salió del sistema', [
            'usuario'        => $e->user,
            'institucion_id' => $e->user->institucion_id,
        ]) : null);
    }
}
