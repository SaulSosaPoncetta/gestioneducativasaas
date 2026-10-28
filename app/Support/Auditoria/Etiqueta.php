<?php

namespace App\Support\Auditoria;

use Illuminate\Database\Eloquent\Model;

/** Texto corto para identificar un registro en la auditoría ("Romero, Sofía"). */
class Etiqueta
{
    public static function de(Model $m): string
    {
        try {
            $texto = match (class_basename($m)) {
                'User', 'Estudiante', 'Tutor', 'Docente' => $m->apellido . ', ' . $m->nombre,
                'CicloLectivo'      => $m->nombre ?: (string) $m->anio,
                'Curso'             => $m->nombre . ' (' . $m->ciclo?->anio . ')',
                'Inscripcion'       => $m->estudiante?->nombre_completo . ' · ' . $m->destino,
                'Asignacion'        => $m->docente?->apellido . ' · ' . $m->espacio?->nombre . ($m->curso ? ' · ' . $m->curso->nombre : ''),
                'Asistencia'        => $m->estudiante?->nombre_completo . ' · ' . $m->fecha?->format('d/m/Y'),
                'Calificacion'      => $m->estudiante?->nombre_completo . ' · ' . $m->espacio?->nombre . ' · ' . $m->periodo?->nombre,
                'Cursada'           => $m->estudiante?->nombre_completo . ' · ' . $m->espacio?->nombre,
                'MesaExamen'        => $m->espacio?->nombre . ' · ' . $m->fecha?->format('d/m/Y'),
                'MesaInscripcion'   => $m->estudiante?->nombre_completo . ' · ' . $m->mesa?->espacio?->nombre,
                'Comunicado'        => $m->titulo,
                'Constancia'        => $m->numero_label . ' · ' . ($m->datos['estudiante']['apellido'] ?? ''),
                default             => $m->nombre ?? $m->titulo ?? null,
            };
        } catch (\Throwable) {
            $texto = null;
        }

        return filled(trim((string) $texto, ' ·,')) ? $texto : '#' . $m->getKey();
    }
}
