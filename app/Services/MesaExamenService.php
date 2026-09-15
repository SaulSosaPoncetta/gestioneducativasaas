<?php

namespace App\Services;

use App\Models\MesaExamen;
use App\Models\NotaFinal;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MesaExamenService
{
    /**
     * Requiere en el Model MesaExamen:
     *
     *   public function docentes()
     *   {
     *       return $this->belongsToMany(Docente::class, 'mesa_examen_docente', 'id_mesa', 'id_docente')
     *           ->withPivot('cargo'); // 'titular' | 'vocal' (no confundir con usuarios.id_rol)
     *   }
     *
     *   public function llamado()
     *   {
     *       return $this->belongsTo(Llamado::class, 'id_llamado', 'id_llamado');
     *   }
     *
     *   public function notasFinal()
     *   {
     *       return $this->hasMany(NotaFinal::class, 'id_mesa', 'id_mesa');
     *   }
     *
     * "estudiantes" ya NO es una relación belongsToMany: se arma en find()
     * a partir de notasFinal, porque notas_final tiene columnas propias
     * (id_materia, id_tipo_archivo) que un attach/sync de pivot no podría
     * completar correctamente.
     */
    protected array $with = [
        'tipoMesa',
        'llamado',
        'materia',
        'materia.carrera',
        'administrador',
        'cicloLectivo',
        'docentes',
        'docentes.usuario',
    ];

    /**
     * Pantalla "Fechas de finales": listado con estado calculado (SIN ALUMNOS /
     * CERRADO / ABIERTA) en base a notasFinal.
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->baseQuery($filters)
            ->withCount('notasFinal as estudiantes_count')
            ->withCount([
                'notasFinal as estudiantes_sin_nota_count' => function ($q) {
                    $q->whereNull('valor');
                },
            ]);

        $mesas = $query->orderBy('fecha')->paginate($perPage);

        $mesas->getCollection()->transform(fn (MesaExamen $mesa) => $this->appendEstado($mesa));

        return $mesas;
    }

    /**
     * Pantalla "Historial de finales". Carrera y Llamado obligatorios (los
     * valida el controller), Año opcional. No calcula 'estado'.
     */
    public function getHistorial(array $filters, int $perPage = 100): LengthAwarePaginator
    {
        return $this->baseQuery($filters)->orderBy('fecha')->paginate($perPage);
    }

    protected function baseQuery(array $filters)
    {
        $query = MesaExamen::query()->with($this->with);

        if (!empty($filters['id_ciclo'])) {
            $query->where('id_ciclo', $filters['id_ciclo']);
        }

        if (!empty($filters['id_tipo_mesa'])) { // tipo de examen: Regular/Libre/Especial/Recuperatorio
            $query->where('id_tipo_mesa', $filters['id_tipo_mesa']);
        }

        if (!empty($filters['id_llamado'])) { // Llamado: Julio/Agosto, Nov/Dic, etc.
            $query->where('id_llamado', $filters['id_llamado']);
        }

        if (!empty($filters['id_materia'])) {
            $query->where('id_materia', $filters['id_materia']);
        }

        if (!empty($filters['id_carrera'])) {
            $query->whereHas('materia', function ($q) use ($filters) {
                $q->where('id_carrera', $filters['id_carrera']);
            });
        }

        if (!empty($filters['anio'])) {
            $query->whereHas('materia', function ($q) use ($filters) {
                $q->where('anio', $filters['anio']);
            });
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('fecha', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('fecha', '<=', $filters['fecha_hasta']);
        }

        if (!empty($filters['aula_desde'])) {
            $query->where('aula', '>=', $filters['aula_desde']);
        }

        if (!empty($filters['aula_hasta'])) {
            $query->where('aula', '<=', $filters['aula_hasta']);
        }

        if (!empty($filters['estudiantes']) && $filters['estudiantes'] !== 'todas') {
            if ($filters['estudiantes'] === 'con') {
                $query->has('notasFinal');
            } elseif ($filters['estudiantes'] === 'sin') {
                $query->doesntHave('notasFinal');
            }
        }

        return $query;
    }

    /**
     * Una mesa está ABIERTA el día del examen y todo el día siguiente
     * (cierra a partir de las 00:00 del segundo día posterior a la fecha).
     * No depende de si tiene alumnos cargados ni de si faltan notas: una
     * mesa recién creada (sin alumnos todavía) ya aparece como ABIERTA.
     */
    protected function appendEstado(MesaExamen $mesa): MesaExamen
    {
        $fechaLimite = optional($mesa->fecha)?->copy()->addDay()->endOfDay();
        $vencida = $fechaLimite && now()->greaterThan($fechaLimite);

        $mesa->estado = $vencida ? 'CERRADO' : 'ABIERTA';

        return $mesa;
    }

    /**
     * Pantalla "Mesa de final": detalle con docentes (presidente/vocal según
     * columna 'cargo'), y estudiantes armados a partir de notasFinal
     * (nota = 'valor', cant = conteo de intentos previos del mismo
     * estudiante para la misma materia).
     */
    public function find(int $id): ?MesaExamen
    {
        $mesa = MesaExamen::with(array_merge($this->with, [
            'notasFinal.estudiante.usuario',
        ]))->find($id);

        if (!$mesa) {
            return null;
        }

        $mesa->presidente = $mesa->docentes->firstWhere('pivot.cargo', 'titular');
        $mesa->vocales = $mesa->docentes->where('pivot.cargo', 'vocal')->values();

        $mesa->estudiantes = $mesa->notasFinal
            // Si hay filas duplicadas para el mismo alumno en esta mesa,
            // nos quedamos con la más reciente (mayor id_nota).
            ->sortByDesc('id_nota')
            ->unique('id_usuario')
            ->map(function (NotaFinal $notaFinal) {
                $estudiante = $notaFinal->estudiante;

                $estudiante->id_nota = $notaFinal->id_nota;
                $estudiante->nota = $notaFinal->valor;
                $estudiante->cant = NotaFinal::where('id_materia', $notaFinal->id_materia)
                    ->where('id_usuario', $notaFinal->id_usuario)
                    ->count();

                return $estudiante;
            })
            ->values();

        return $mesa;
    }

    public function findOrFail(int $id): MesaExamen
    {
        $mesa = $this->find($id);

        abort_if(!$mesa, 404, 'Mesa de examen no encontrada');

        return $mesa;
    }

    /**
     * Pantalla "Agregar Final". Crea 1 o 2 mesas (una por fecha cargada).
     * Los docentes se sincronizan con su cargo ('titular' / 'vocal').
     *
     * 'habilitado' no se pide en el form de "Agregar Final": toda mesa
     * nueva se crea habilitada (ABIERTA) por default, se pise o no algo
     * distinto en $data.
     */
    public function create(array $data): Collection
    {
        $fechas = $data['fechas'];
        unset($data['fechas']);

        $docenteTitular = $data['docente_titular'] ?? null;
        $docenteVocal = $data['docente_vocal'] ?? null;
        unset($data['docente_titular'], $data['docente_vocal']);

        $data['habilitado'] = true;

        return DB::transaction(function () use ($data, $fechas, $docenteTitular, $docenteVocal) {
            $mesasCreadas = collect();

            foreach ($fechas as $fecha) {
                $mesa = MesaExamen::create(array_merge($data, ['fecha' => $fecha]));

                $docentesSync = [];
                if ($docenteTitular) {
                    $docentesSync[$docenteTitular] = ['cargo' => 'titular'];
                }
                if ($docenteVocal) {
                    $docentesSync[$docenteVocal] = ['cargo' => 'vocal'];
                }
                if (!empty($docentesSync)) {
                    $mesa->docentes()->sync($docentesSync);
                }

                $mesasCreadas->push($mesa->load($this->with));
            }

            return $mesasCreadas;
        });
    }

    /**
     * Edición administrativa (materia/docentes/aula/fecha/ciclo). No es lo
     * que dispara el botón "Modificar" de "Mesa de final" (eso es cargarNotas()).
     *
     * OJO: $mesa suele venir "decorado" por find() (le agrega presidente,
     * vocales, estudiantes como propiedades sueltas, no columnas reales).
     * Eloquent las trata como atributos sucios y las mete en el UPDATE si
     * se llama ->update() directo sobre esa instancia. Por eso se usa
     * fresh() acá: trae una copia limpia desde la base antes de guardar.
     */
    public function update(MesaExamen $mesa, array $data): MesaExamen
    {
        $docenteTitular = $data['docente_titular'] ?? null;
        $docenteVocal = $data['docente_vocal'] ?? null;
        unset($data['docente_titular'], $data['docente_vocal']);

        $mesaLimpia = $mesa->fresh();
        $mesaLimpia->update($data);

        if ($docenteTitular || $docenteVocal) {
            $docentesSync = [];
            if ($docenteTitular) {
                $docentesSync[$docenteTitular] = ['cargo' => 'titular'];
            }
            if ($docenteVocal) {
                $docentesSync[$docenteVocal] = ['cargo' => 'vocal'];
            }
            $mesaLimpia->docentes()->sync($docentesSync);
        }

        return $this->find($mesaLimpia->id_mesa);
    }

    /**
     * Pantalla "Agregar Alumno a final". Crea la fila en notas_final sin
     * 'valor' todavía (para que el estudiante quede listado en la mesa).
     * 'fecha_carga' es NOT NULL en la base (sin default), así que se setea
     * igual aunque todavía no haya nota; se vuelve a pisar cuando se cargue
     * la nota real en cargarNotas().
     */
    public function addEstudiantes(MesaExamen $mesa, array $idsEstudiantes): MesaExamen
    {
        foreach ($idsEstudiantes as $idUsuario) {
            NotaFinal::firstOrCreate(
                [
                    'id_mesa'    => $mesa->id_mesa,
                    'id_usuario' => $idUsuario,
                ],
                [
                    'id_materia'  => $mesa->id_materia,
                    'valor'       => null,
                    'fecha_carga' => now(),
                ]
            );
        }

        return $this->find($mesa->id_mesa);
    }

    /**
     * Guarda notas. $notasPorEstudiante: [ id_usuario => valor, ... ].
     * Si el estudiante ya tenía fila en notas_final para esta mesa, la
     * actualiza; si no, la crea (por si "Cargar notas" se usa sin haber
     * pasado antes por "Agregar Alumno a final").
     */
    public function cargarNotas(MesaExamen $mesa, array $notasPorEstudiante): MesaExamen
    {
        foreach ($notasPorEstudiante as $idUsuario => $valor) {
            if ($valor === null || $valor === '') {
                continue;
            }

            NotaFinal::updateOrCreate(
                [
                    'id_mesa'    => $mesa->id_mesa,
                    'id_usuario' => $idUsuario,
                ],
                [
                    'id_materia'  => $mesa->id_materia,
                    'valor'       => $valor,
                    'fecha_carga' => now(),
                ]
            );
        }

        return $this->find($mesa->id_mesa);
    }
}