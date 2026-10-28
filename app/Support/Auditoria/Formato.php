<?php

namespace App\Support\Auditoria;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/** Nombres de campos y valores legibles para la pantalla de auditoría. */
class Formato
{
    private const CAMPOS = [
        'institucion_id'        => 'Institución',
        'institucion_nivel_id'  => 'Nivel',
        'ciclo_lectivo_id'      => 'Ciclo lectivo',
        'curso_id'              => 'Curso',
        'plan_estudio_id'       => 'Plan / carrera',
        'espacio_curricular_id' => 'Espacio curricular',
        'periodo_evaluacion_id' => 'Período',
        'estudiante_id'         => 'Estudiante',
        'docente_id'            => 'Docente',
        'user_id'               => 'Usuario',
        'preceptor_id'          => 'Preceptor/a',
        'mesa_examen_id'        => 'Mesa',
        'autor_id'              => 'Autor/a',
        'dni'                   => 'DNI',
        'cue'                   => 'CUE',
        'cuit'                  => 'CUIT',
        'cuil'                  => 'CUIL',
        'email'                 => 'Email',
        'password'              => 'Contraseña',
        'rol'                   => 'Rol',
        'activo'                => 'Activo',
        'anio'                  => 'Año',
        'grado'                 => 'Grado / año',
        'division'              => 'División',
        'valor'                 => 'Nota',
        'valor_numerico'        => 'Nota (número)',
        'justificada'           => 'Justificada',
        'publicado_at'          => 'Publicado',
        'anulada_at'            => 'Anulada',
        'fecha_baja'            => 'Fecha de baja',
        'motivo_baja'           => 'Motivo de baja',
        'motivo_anulacion'      => 'Motivo de anulación',
        'telefono'              => 'Teléfono',
        'fecha_nacimiento'      => 'Fecha de nacimiento',
        'fecha_ingreso'         => 'Fecha de ingreso',
        'genero'                => 'Género',
        'condicion'             => 'Condición',
        'resolucion'            => 'Resolución',
        'situacion'             => 'Situación',
        'observacion'           => 'Observación',
        'nombre_corto'          => 'Nombre corto',
        'tipo_documento'        => 'Tipo de documento',
        'recibe_notificaciones' => 'Recibe avisos',
    ];

    public static function campo(string $campo): string
    {
        return self::CAMPOS[$campo] ?? Str::ucfirst(str_replace('_', ' ', preg_replace('/(_id|_at)$/', '', $campo)));
    }

    public static function valor(mixed $valor): string
    {
        return match (true) {
            $valor === null, $valor === ''        => '—',
            is_bool($valor)                        => $valor ? 'Sí' : 'No',
            is_array($valor)                       => json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            is_string($valor) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}(:\d{2})?)?$/', $valor)
                                                   => self::fecha($valor),
            default                                => Str::limit((string) $valor, 300),
        };
    }

    private static function fecha(string $valor): string
    {
        $c = Carbon::parse($valor);

        return strlen($valor) > 10 && $c->format('H:i') !== '00:00' ? $c->format('d/m/Y H:i') : $c->format('d/m/Y');
    }
}
