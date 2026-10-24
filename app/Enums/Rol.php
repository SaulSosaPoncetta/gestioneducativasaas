<?php

namespace App\Enums;

/**
 * Roles de usuario del sistema.
 *
 * - superadmin: equipo MiGestión; administra todas las instituciones.
 * - admin: administrador de una institución (dueño de la cuenta SaaS).
 * - directivo / secretario / preceptor: personal de conducción y administrativo.
 * - docente: carga asistencia, notas e informes de sus espacios.
 * - tutor: madre, padre o responsable de uno o más estudiantes.
 * - estudiante: acceso al propio legajo, notas, inscripciones y finales.
 */
enum Rol: string
{
    case SuperAdmin = 'superadmin';
    case Admin      = 'admin';
    case Directivo  = 'directivo';
    case Secretario = 'secretario';
    case Preceptor  = 'preceptor';
    case Docente    = 'docente';
    case Tutor      = 'tutor';
    case Estudiante = 'estudiante';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super administrador',
            self::Admin      => 'Administrador',
            self::Directivo  => 'Directivo',
            self::Secretario => 'Secretario/a',
            self::Preceptor  => 'Preceptor/a',
            self::Docente    => 'Docente',
            self::Tutor      => 'Tutor/a',
            self::Estudiante => 'Estudiante',
        };
    }

    /** Personal de la institución con acceso a la gestión administrativa. */
    public function esGestion(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::Directivo, self::Secretario], true);
    }

    /** Roles que puede asignar un administrador de institución. */
    public static function asignables(): array
    {
        return array_values(array_filter(self::cases(), fn (self $r) => $r !== self::SuperAdmin));
    }

    public static function opciones(): array
    {
        return collect(self::asignables())->mapWithKeys(fn (self $r) => [$r->value => $r->label()])->all();
    }
}
