<?php

namespace App\Support;

use App\Models\Institucion;

/**
 * Institución con la que se está trabajando en el request actual.
 *
 * La define el middleware ResolverInstitucion (la del usuario o, para el
 * superadmin, la que eligió en el panel). Los modelos con el trait
 * PerteneceAInstitucion filtran y completan institucion_id con esto.
 */
class InstitucionActual
{
    protected ?Institucion $institucion = null;

    public static function instancia(): self
    {
        if (! app()->bound(self::class)) {
            app()->instance(self::class, new self());
        }

        return app(self::class);
    }

    public static function establecer(?Institucion $institucion): void
    {
        self::instancia()->institucion = $institucion;
    }

    public static function get(): ?Institucion
    {
        return self::instancia()->institucion;
    }

    public static function id(): ?int
    {
        return self::instancia()->institucion?->id;
    }

    public static function existe(): bool
    {
        return self::instancia()->institucion !== null;
    }
}
