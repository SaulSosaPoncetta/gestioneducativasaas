<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
    }
}
