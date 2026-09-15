<?php

namespace App\Services;

use App\Models\Carrera;
use App\Models\CicloLectivo;
use App\Models\Docente;
use App\Models\EstadoEstudiante;
use App\Models\Estudiante;
use App\Models\InscripcionMateria;
use App\Models\Materia;
use App\Models\MateriaDocente;
use App\Models\NotaCuatrimestre;
use App\Models\TipoEstudiante;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MateriaService
{
    // -------------------------------------------------------------------------
    // CURSADA (perfil de carrera): materias agrupadas por año curricular
    // -------------------------------------------------------------------------

    public function materiasPorCarreraAgrupadas(Carrera $carrera): array
    {
        $materias = Materia::where('id_carrera', $carrera->id_carrera)
            ->with(['materiaDocentes' => function ($q) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now());
            }, 'materiaDocentes.docente.usuario'])
            ->orderBy('anio')
            ->orderBy('orden_anio')
            ->get();

        $agrupadas = [];

        foreach (range(1, 5) as $anio) {
            $agrupadas[Materia::nombreAnio($anio)] = $materias
                ->where('anio', $anio)
                ->values()
                ->map(function (Materia $materia) {
                    $docente = $materia->materiaDocentes->first()?->docente;

                    return (object) [
                        'id_materia' => $materia->id_materia,
                        'nombre'     => $materia->nombre,
                        'profesor'   => $docente ? (object) [
                            'usuario' => (object) ['apellido' => $docente->usuario->apellido],
                        ] : null,
                    ];
                });
        }

        return $agrupadas;
    }

    // -------------------------------------------------------------------------
    // ALTA / EDICIÓN DE MATERIA
    // -------------------------------------------------------------------------

    public function crear(Carrera $carrera, array $data): Materia
    {
        $tipoMateria = \App\Models\TipoMateria::firstOrCreate(['descripcion_tipo_materia' => 'Materia']);

        return Materia::create(array_merge($data, [
            'id_carrera'         => $carrera->id_carrera,
            'id_tipo_materia'    => $tipoMateria->id_tipo_materia,
            'nota_promocionable' => 7,
            'nota_aprobada'      => 4,
        ]));
    }

    public function getById(int $id): Materia
    {
        return Materia::with([
            'carrera',
            'planEstudio',
            'materiaDocentes.docente.usuario',
            'materiaDocentes.docente.usuario.dnis',
        ])->findOrFail($id);
    }

    public function actualizar(int $id, array $data): Materia
    {
        $materia = Materia::findOrFail($id);
        $materia->update($data);

        return $materia->fresh(['carrera', 'planEstudio']);
    }

    // -------------------------------------------------------------------------
    // PROFESORES ASIGNADOS
    // -------------------------------------------------------------------------

    public function docentesDisponibles(Materia $materia): Collection
    {
        $yaAsignados = $materia->materiaDocentes()->pluck('id_usuario_docente');

        return Docente::with('usuario')
            ->whereNotIn('id_usuario', $yaAsignados)
            ->get()
            ->sortBy(fn ($d) => $d->usuario->apellido);
    }

    public function asignarDocente(Materia $materia, array $data): MateriaDocente
    {
        return DB::transaction(function () use ($materia, $data) {
            return MateriaDocente::create([
                'id_materia'         => $materia->id_materia,
                'id_usuario_docente' => $data['id_docente'],
                'situacion_revista'  => $data['situacion_revista'] ?? null,
                'division'           => $data['division'] ?? null,
                'fecha_inicio'       => $data['fecha_inicio'] ?? null,
                'fecha_fin'          => $data['fecha_fin'] ?? null,
                'horas_semanales'    => $data['horas_semanales'] ?? null,
            ]);
        });
    }

    public function quitarDocente(Materia $materia, int $idDocente): void
    {
        MateriaDocente::where('id_materia', $materia->id_materia)
            ->where('id_usuario_docente', $idDocente)
            ->delete();
    }

    // -------------------------------------------------------------------------
    // CICLO LECTIVO ACTIVO
    // -------------------------------------------------------------------------

    public function cicloLectivoActivo(): ?CicloLectivo
    {
        return CicloLectivo::where('fecha_fin', '>=', now())
            ->orderBy('fecha_inicio')
            ->first()
            ?? CicloLectivo::orderByDesc('fecha_inicio')->first();
    }

    // -------------------------------------------------------------------------
    // ALUMNOS DE LA MATERIA (agregar / cargar notas)
    // -------------------------------------------------------------------------

    public function alumnosDisponibles(Materia $materia, ?string $busqueda = null): Collection
    {
        $idsInscriptos = InscripcionMateria::where('id_materia', $materia->id_materia)
            ->pluck('id_usuario');

        $query = Estudiante::with(['usuario', 'usuario.dnis'])
            ->join('estudiante_carrera', 'estudiantes.id_usuario', '=', 'estudiante_carrera.id_usuario')
            ->join('usuarios', 'estudiantes.id_usuario', '=', 'usuarios.id_usuario')
            ->where('estudiante_carrera.id_carrera', $materia->id_carrera)
            ->whereNotIn('estudiantes.id_usuario', $idsInscriptos)
            ->select('estudiantes.*')
            ->distinct();

        if (!empty($busqueda)) {
            $like = '%' . $busqueda . '%';
            $query->where(function ($q) use ($like) {
                $q->where('usuarios.nombre', 'like', $like)
                  ->orWhere('usuarios.apellido', 'like', $like);
            });
        }

        return $query->orderBy('usuarios.apellido')->get();
    }

    public function alumnosAsignados(Materia $materia): Collection
    {
        return InscripcionMateria::with(['estudiante.usuario', 'estudiante.usuario.dnis'])
            ->where('id_materia', $materia->id_materia)
            ->get();
    }

    public function sincronizarAlumnos(Materia $materia, array $idsUsuario): void
    {
        $ciclo = $this->cicloLectivoActivo();

        if (!$ciclo) {
            throw new \RuntimeException('No hay ningún ciclo lectivo cargado. Cargá uno antes de agregar alumnos a la materia.');
        }

        DB::transaction(function () use ($materia, $idsUsuario, $ciclo) {

            // Baja de los que ya no están en la lista enviada
            InscripcionMateria::where('id_materia', $materia->id_materia)
                ->where('id_ciclo', $ciclo->id_ciclo)
                ->whereNotIn('id_usuario', $idsUsuario)
                ->delete();

            $tipoRegular  = TipoEstudiante::firstOrCreate(['descripcion_tipo' => 'Regular']);
            $estadoActivo = EstadoEstudiante::firstOrCreate(['descripcion_estado' => 'Activo']);

            foreach ($idsUsuario as $idUsuario) {
                InscripcionMateria::firstOrCreate([
                    'id_usuario' => $idUsuario,
                    'id_materia' => $materia->id_materia,
                    'id_ciclo'   => $ciclo->id_ciclo,
                ], [
                    'fecha_inscripcion'    => now(),
                    'id_tipo_estudiante'   => $tipoRegular->id_tipo_estudiante,
                    'id_estado_estudiante' => $estadoActivo->id_estado_estudiante,
                ]);
            }
        });
    }

    // -------------------------------------------------------------------------
    // CARGA DE NOTAS
    // -------------------------------------------------------------------------

    /**
     * Devuelve, por cada alumno inscripto, sus notas de 1°C / 2°C / Rec.-Prefinal
     * y si tiene un cierre de cursada cargado (reprobado directo o cierre normal).
     */
    public function datosCargarNotas(Materia $materia): array
    {
        $ciclo = $this->cicloLectivoActivo();

        $inscripciones = InscripcionMateria::with('estudiante.usuario')
            ->where('id_materia', $materia->id_materia)
            ->when($ciclo, fn ($q) => $q->where('id_ciclo', $ciclo->id_ciclo))
            ->get();

        $notas = NotaCuatrimestre::where('id_materia', $materia->id_materia)
            ->when($ciclo, fn ($q) => $q->where('id_ciclo', $ciclo->id_ciclo))
            ->get()
            ->groupBy('id_usuario');

        $cursando = [];
        $reprobados = [];

        foreach ($inscripciones as $inscripcion) {
            $notasAlumno = $notas->get($inscripcion->id_usuario, collect())
                ->keyBy('periodo');

            $fila = (object) [
                'id_usuario' => $inscripcion->id_usuario,
                'nombre'     => $inscripcion->estudiante->usuario->apellido . ', ' . $inscripcion->estudiante->usuario->nombre,
                'primerC'    => $notasAlumno->get('1C'),
                'segundoC'   => $notasAlumno->get('2C'),
                'recPrefinal'=> $notasAlumno->get('REC'),
                'cierre'     => $notasAlumno->get('CIERRE'),
            ];

            if ($fila->cierre && $fila->cierre->situacion === 'reprobado_directo') {
                $reprobados[] = $fila;
            } else {
                $cursando[] = $fila;
            }
        }

        return compact('cursando', 'reprobados');
    }

    /**
     * Guarda las notas cargadas para cada alumno. Si $cierreAutomatico está
     * activo, calcula el cierre de cursada en base a la situación de ambos
     * cuatrimestres.
     */
    public function guardarNotas(Materia $materia, array $filas, bool $cierreAutomatico): void
    {
        $ciclo = $this->cicloLectivoActivo();

        DB::transaction(function () use ($materia, $filas, $cierreAutomatico, $ciclo) {
            foreach ($filas as $fila) {
                $idUsuario = $fila['id_usuario'];

                foreach (['1C', '2C', 'REC'] as $periodo) {
                    if (!isset($fila[$periodo])) {
                        continue;
                    }

                    $this->guardarNotaPeriodo($materia, $idUsuario, $ciclo, $periodo, $fila[$periodo]);
                }

                if ($cierreAutomatico) {
                    $cierre = $this->calcularCierre($fila, $materia->promocionable);

                    if ($cierre['situacion']) {
                        $this->guardarNotaPeriodo($materia, $idUsuario, $ciclo, 'CIERRE', [
                            'valor'     => $cierre['valor'],
                            'situacion' => $cierre['situacion'],
                        ]);
                    }
                } elseif (!empty($fila['CIERRE']['situacion'])) {
                    // Cierre automático desactivado: se respeta lo que la
                    // secretaría cargó a mano en la columna Cierre.
                    $this->guardarNotaPeriodo($materia, $idUsuario, $ciclo, 'CIERRE', $fila['CIERRE']);
                }
            }
        });
    }

    private function guardarNotaPeriodo(Materia $materia, int $idUsuario, ?CicloLectivo $ciclo, string $periodo, array $datos): void
    {
        NotaCuatrimestre::updateOrCreate([
            'id_materia' => $materia->id_materia,
            'id_usuario' => $idUsuario,
            'id_ciclo'   => $ciclo?->id_ciclo,
            'periodo'    => $periodo,
        ], [
            // 'valor' es NOT NULL en la tabla original: cuando solo se carga
            // la situación (ej. el cierre automático) se guarda 0 en vez de nulo.
            'valor'      => $datos['valor'] ?? 0,
            'situacion'  => $datos['situacion'] ?? null,
            'fecha_carga'=> now(),
        ]);
    }

    /**
     * Calcula el cierre de cursada (situación + nota promedio) a partir de
     * las notas cargadas de 1°C, 2°C y, si hizo falta, el recuperatorio
     * (Rec./Prefinal). Devuelve ['situacion' => ?string, 'valor' => ?float].
     *
     * Reglas de SITUACIÓN (nunca se deciden por el número, solo por estas
     * categorías — un promedio de 7+ no implica promoción si no se cumplió
     * la condición real de promoción en cada cuatrimestre):
     *
     *  - Un solo cuatrimestre cargado (la materia es de un solo cuatrimestre,
     *    o el otro todavía no se cargó): el cierre es esa misma situación.
     *    Si esa única situación fue Desaprobado y hay recuperatorio cargado,
     *    el recuperatorio decide (Aprobado o Desaprobado).
     *  - Desaprobado + Desaprobado                  => Desaprobado
     *  - Aprobado + Desaprobado (cualquier orden)    => pendiente de examen
     *    flotante hasta que se cargue el recuperatorio; ahí:
     *      · Rec. aprobado    => Aprobado
     *      · Rec. desaprobado => Desaprobado
     *  - Aprobado + Aprobado                         => Aprobado
     *  - Promocionado + Aprobado (cualquier orden)   => Aprobado
     *  - Promocionado + Promocionado                 => Promocionado
     *  - Promoción directa + Promoción directa       => Promoción directa
     *    (solo aplica a materias marcadas como "promocionable")
     *  - Promoción directa + cualquier otra cosa     => Desaprobado
     *    (la promoción directa es "sí o sí": si un cuatrimestre no llega,
     *    no hay término medio)
     *
     * Reglas de PROMEDIO (numérico, independiente de la situación):
     *  - Promedia los cuatrimestres que tengan nota cargada.
     *  - Si hubo recuperatorio (porque un cuatrimestre se perdió), el
     *    recuperatorio REEMPLAZA la nota del cuatrimestre perdido en el
     *    promedio (no se promedian los 3 juntos) — el promedio queda entre
     *    el cuatrimestre aprobado y el recuperatorio.
     */
    private function calcularCierre(array $fila, bool $materiaPromocionDirecta): array
    {
        $s1 = $fila['1C']['situacion'] ?? null;
        $s2 = $fila['2C']['situacion'] ?? null;
        $sRec = $fila['REC']['situacion'] ?? null;

        $v1 = $this->valorNumerico($fila['1C']['valor'] ?? null);
        $v2 = $this->valorNumerico($fila['2C']['valor'] ?? null);
        $vRec = $this->valorNumerico($fila['REC']['valor'] ?? null);

        // Ninguno de los dos cuatrimestres tiene situación cargada: nada que cerrar.
        if (!$s1 && !$s2) {
            return ['situacion' => null, 'valor' => null];
        }

        // Un solo cuatrimestre cargado (materia de un cuatrimestre, o falta el otro)
        if (!$s1 || !$s2) {
            $situacionUnica = $s1 ?? $s2;
            $valorUnico = $s1 ? $v1 : $v2;

            if ($situacionUnica === 'desaprobado' && $sRec) {
                return [
                    'situacion' => $sRec === 'aprobado' ? 'aprobado' : 'desaprobado',
                    'valor'     => $this->promediar([$valorUnico, $vRec]),
                ];
            }

            return ['situacion' => $situacionUnica, 'valor' => $valorUnico];
        }

        $situaciones = [$s1, $s2];

        // Ambos cuatrimestres con la misma situación
        if ($s1 === $s2) {
            $situacionFinal = match ($s1) {
                'desaprobado'        => 'desaprobado',
                'aprobado'           => 'aprobado',
                'promocionado'       => 'promocionado',
                'promocion_directa'  => 'promocion_directa',
                default              => null,
            };

            return ['situacion' => $situacionFinal, 'valor' => $this->promediar([$v1, $v2])];
        }

        // Promoción directa mezclada con cualquier otra situación => Desaprobado (es "sí o sí")
        if (in_array('promocion_directa', $situaciones, true)) {
            return ['situacion' => 'desaprobado', 'valor' => $this->promediar([$v1, $v2])];
        }

        // Promocionado + Aprobado => Aprobado
        if (in_array('promocionado', $situaciones, true) && in_array('aprobado', $situaciones, true)) {
            return ['situacion' => 'aprobado', 'valor' => $this->promediar([$v1, $v2])];
        }

        // Aprobado + Desaprobado, o Promocionado + Desaprobado => examen flotante
        // hasta que se cargue el Rec. El Rec. reemplaza (no se suma a) la nota
        // del cuatrimestre perdido para el promedio.
        if (in_array('desaprobado', $situaciones, true)) {
            $valorDelQueAproboo = $s1 === 'desaprobado' ? $v2 : $v1;

            if ($sRec === 'aprobado') {
                return ['situacion' => 'aprobado', 'valor' => $this->promediar([$valorDelQueAproboo, $vRec])];
            }

            if ($sRec === 'desaprobado') {
                return ['situacion' => 'desaprobado', 'valor' => $this->promediar([$valorDelQueAproboo, $vRec])];
            }

            return ['situacion' => 'examen_flotante', 'valor' => null];
        }

        return ['situacion' => null, 'valor' => null];
    }

    private function valorNumerico($valor): ?float
    {
        return ($valor !== null && $valor !== '') ? (float) $valor : null;
    }

    private function promediar(array $valores): ?float
    {
        $valores = array_filter($valores, fn ($v) => $v !== null);

        if (empty($valores)) {
            return null;
        }

        return round(array_sum($valores) / count($valores), 2);
    }

    public function reprobarDirecto(Materia $materia, int $idUsuario): void
    {
        $ciclo = $this->cicloLectivoActivo();

        $this->guardarNotaPeriodo($materia, $idUsuario, $ciclo, 'CIERRE', [
            'valor'     => null,
            'situacion' => 'reprobado_directo',
        ]);
    }

    public function restaurarCursando(Materia $materia, int $idUsuario): void
    {
        $ciclo = $this->cicloLectivoActivo();

        NotaCuatrimestre::where('id_materia', $materia->id_materia)
            ->where('id_usuario', $idUsuario)
            ->when($ciclo, fn ($q) => $q->where('id_ciclo', $ciclo->id_ciclo))
            ->where('periodo', 'CIERRE')
            ->delete();
    }
}
