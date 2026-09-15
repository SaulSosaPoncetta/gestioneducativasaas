<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Carrera;
use App\Models\CicloLectivo;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Llamado;
use App\Models\Materia;
use App\Models\MesaExamen;
use App\Models\TipoMesaExamen;
use App\Services\MesaExamenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MesaExamenController extends Controller
{
    public function __construct(protected MesaExamenService $mesaExamenService)
    {
    }

    /**
     * GET /finalesHistorial  (name: finalesHistorial)
     * Vista "Historial de finales" con datos reales.
     */
    public function historialView(): View
    {
        $carreras = Carrera::where('nombre', '!=', 'Sin asignar')
            ->orderBy('nombre')
            ->get(['id_carrera', 'nombre']);

        $llamados = Llamado::orderBy('id_llamado')
            ->get(['id_llamado', 'descripcion'])
            ->map(fn ($l) => (object) ['id_llamado' => $l->id_llamado, 'nombre' => $l->descripcion]);

        $anios = [1, 2, 3, 4, 5];

        $mesas = MesaExamen::with(['materia', 'materia.carrera', 'docentes.usuario'])
            ->withCount('notasFinal as estudiantes_count')
            ->withCount([
                'notasFinal as estudiantes_sin_nota_count' => function ($q) {
                    $q->whereNull('valor');
                },
            ])
            ->orderBy('fecha')
            ->get()
            ->filter(function (MesaExamen $mesa) {
                // CERRADO = tiene alumnos y todos con nota cargada
                return ($mesa->estudiantes_count ?? 0) > 0
                    && ($mesa->estudiantes_sin_nota_count ?? 0) === 0;
            })
            ->map(function (MesaExamen $mesa) {
                return (object) [
                    'id_mesa'    => $mesa->id_mesa,
                    'id_carrera' => $mesa->materia->id_carrera ?? null,
                    'carrera'    => $mesa->materia->carrera->nombre ?? '',
                    'id_llamado' => $mesa->id_llamado,
                    'anio'       => $mesa->materia->anio ?? null,
                    'fecha_hora' => optional($mesa->fecha)->format('d/m/Y H:i') . ' hs',
                    'espacio'    => $mesa->materia->nombre ?? '',
                    'profesores' => $mesa->docentes
                        ->map(fn ($d) => $d->usuario->apellido ?? '')
                        ->filter()
                        ->implode(', '),
                ];
            })
            ->values();

        return view('Secretaria.finalesHistorial', compact('carreras', 'llamados', 'anios', 'mesas'));
    }

    /**
     * GET /finalesSec  (name: finalesSec)
     * Vista "Fechas de finales" con datos reales (MesaExamenService::getAll,
     * que ya calcula 'estado': SIN ALUMNOS / CERRADO / ABIERTA).
     * Los filtros de Carrera/Año se resuelven server-side vía querystring
     * (?id_carrera=&anio=), reusando los mismos filtros que ya soporta
     * MesaExamenService::getAll().
     */
    public function finalesView(Request $request): View
    {
        $filters = $request->only([
            'id_ciclo',
            'id_tipo_mesa',
            'id_llamado',
            'id_materia',
            'id_carrera',
            'anio',
            'fecha_desde',
            'fecha_hasta',
            'aula_desde',
            'aula_hasta',
            'estudiantes',
        ]);

        $mesasPaginadas = $this->mesaExamenService->getAll($filters, 50);

        $mesas = $mesasPaginadas->getCollection()
            ->filter(fn ($mesa) => $mesa->estado === 'ABIERTA')
            ->map(function ($mesa) {
                return (object) [
                    'id_mesa'    => $mesa->id_mesa,
                    'fecha_hora' => optional($mesa->fecha)->format('H:i - d/m/Y'),
                    'espacio'    => $mesa->materia->nombre ?? '',
                    'carrera'    => $mesa->materia->carrera->nombre ?? '',
                    'anio'       => ($mesa->materia->anio ?? '') . '°',
                    'profesores' => $mesa->docentes
                        ->map(fn ($d) => $d->usuario->apellido ?? '')
                        ->filter()
                        ->implode(', '),
                ];
            })
            ->values();

        $carreras = Carrera::where('nombre', '!=', 'Sin asignar')
            ->orderBy('nombre')
            ->get(['id_carrera', 'nombre']);
        $anios = [1, 2, 3, 4, 5];

        return view('Secretaria.finales', compact('mesas', 'carreras', 'anios', 'filters'));
    }

    /**
     * GET /finalPerfil/{id}  (name: finalPerfil)
     * Vista "Mesa de final" con datos reales.
     *
     * Además de los valores formateados para mostrar, expone los ids
     * crudos (id_materia, id_presidente, id_vocal, fecha_input/hora_input)
     * y las listas $materias/$docentes, necesarios para que el botón
     * "Modificar" también permita editar los datos de la mesa (no solo
     * las notas).
     */
    public function showView(int $id): View
    {
        $mesaModel = $this->mesaExamenService->find($id);

        abort_if(!$mesaModel, 404, 'Mesa de examen no encontrada');

        $mesa = (object) [
            'id_mesa'         => $mesaModel->id_mesa,

            // Valores formateados para mostrar
            'espacio'         => $mesaModel->materia->nombre ?? '',
            'fecha'           => optional($mesaModel->fecha)->format('d/m/y'),
            'aula'            => $mesaModel->aula ?? '',
            'presidente_mesa' => $mesaModel->presidente->usuario->apellido ?? '',
            'vocales'         => $mesaModel->vocales
                ->map(fn ($v) => $v->usuario->apellido ?? '')
                ->filter()
                ->values()
                ->all(),

            // Valores crudos para poder editar
            'id_materia'      => $mesaModel->id_materia,
            'fecha_input'     => optional($mesaModel->fecha)->format('Y-m-d'),
            'hora_input'      => optional($mesaModel->fecha)->format('H:i'),
            'id_presidente'   => optional($mesaModel->presidente)->id_usuario,
            // Solo se edita UN vocal (limitación actual del service: ver
            // MesaExamenService::update()/create(), que solo soportan un
            // único docente_vocal).
            'id_vocal'        => optional($mesaModel->vocales->first())->id_usuario,
        ];

        $alumnosFinal = $mesaModel->estudiantes->map(function ($estudiante) {
            return (object) [
                'id_usuario' => $estudiante->id_usuario,
                'nombre'     => trim(($estudiante->usuario->apellido ?? '') . ', ' . ($estudiante->usuario->nombre ?? ''), ', '),
                'nota'       => $estudiante->nota,
                'cant'       => $estudiante->cant,
            ];
        });

        // Listas para los selects de edición (mismo patrón que agregarFechaForm())
        $materias = Materia::orderBy('nombre')->get(['id_materia', 'nombre']);
        $docentes = Docente::with('usuario')
            ->get()
            ->map(fn ($d) => (object) [
                'id_usuario' => $d->id_usuario,
                'nombre'     => trim(($d->usuario->apellido ?? '') . ', ' . ($d->usuario->nombre ?? ''), ', '),
            ]);

        return view('Secretaria.finalPerfil', compact('mesa', 'alumnosFinal', 'materias', 'docentes'));
    }

    /**
     * GET /CargarNotasFinal/{id}  (name: cargarNotasFinal)
     * Vista "Cargar notas final" con datos reales.
     */
    public function cargarNotasView(int $id): View
    {
        $mesaModel = $this->mesaExamenService->find($id);

        abort_if(!$mesaModel, 404, 'Mesa de examen no encontrada');

        $mesa = (object) [
            'id_mesa' => $mesaModel->id_mesa,
            'fecha'   => optional($mesaModel->fecha)->format('d/m/Y'),
            'hora'    => optional($mesaModel->fecha)->format('H:i') . ' hs',
            'materia' => $mesaModel->materia->nombre ?? '',
            'anio'    => ($mesaModel->materia->anio ?? '') . '°',
        ];

        $alumnosFinal = $mesaModel->estudiantes
            ->whereNull('nota')
            ->map(function ($estudiante) {
                return (object) [
                    'id_usuario' => $estudiante->id_usuario,
                    'nombre'     => trim(($estudiante->usuario->apellido ?? '') . ', ' . ($estudiante->usuario->nombre ?? ''), ', '),
                    'cant'       => $estudiante->cant,
                ];
            })
            ->values();

        return view('Secretaria.cargarNotasFinal', compact('mesa', 'alumnosFinal'));
    }

    /**
     * GET /finalesAgregarFecha  (name: finalesAgregarFecha)
     * Formulario "Agregar Final". Sirve los combos que necesita el form
     * (materia, tipo de mesa, llamado, ciclo lectivo, docentes, administrador).
     * El submit va contra store() (POST /finalesAgregarFecha).
     */
    public function agregarFechaForm(): View
    {
        $carreras = Carrera::where('nombre', '!=', 'Sin asignar')
            ->orderBy('nombre')
            ->get(['id_carrera', 'nombre']);
        $materias = Materia::orderBy('nombre')->get(['id_materia', 'nombre', 'id_carrera']);
        $tiposMesa = TipoMesaExamen::orderBy('id_tipo_mesa')->get(['id_tipo_mesa', 'descripcion']);
        $llamados = Llamado::orderBy('id_llamado')->get(['id_llamado', 'descripcion']);
        $ciclos = CicloLectivo::orderBy('id_ciclo', 'desc')->get(['id_ciclo', 'anio']);
        $docentes = Docente::with('usuario')
            ->get()
            ->map(fn ($d) => (object) [
                'id_usuario' => $d->id_usuario,
                'nombre'     => trim(($d->usuario->apellido ?? '') . ', ' . ($d->usuario->nombre ?? ''), ', '),
            ]);
        $administradores = Administrador::with('usuario')
            ->get()
            ->map(fn ($a) => (object) [
                'id_usuario' => $a->id_usuario,
                'nombre'     => trim(($a->usuario->apellido ?? '') . ', ' . ($a->usuario->nombre ?? ''), ', '),
            ]);

        return view('Secretaria.finalesAgregarFecha', compact(
            'carreras',
            'materias',
            'tiposMesa',
            'llamados',
            'ciclos',
            'docentes',
            'administradores'
        ));
    }

    /**
     * GET /finales/{id}/agregarEstudiante  (name: finalesAgregarEst)
     * Formulario "Agregar Alumno a final". Necesita el id de la mesa.
     * El submit va contra addEstudiantes() (POST /finales/{id}/estudiantes).
     */
    public function agregarEstudianteForm(int $id): View
    {
        $mesaModel = $this->mesaExamenService->find($id);

        abort_if(!$mesaModel, 404, 'Mesa de examen no encontrada');

        $idsYaAgregados = $mesaModel->estudiantes->pluck('id_usuario');

        $mesa = (object) [
            'id_mesa' => $mesaModel->id_mesa,
            'materia' => $mesaModel->materia->nombre ?? '',
            'fecha'   => optional($mesaModel->fecha)->format('d/m/Y H:i'),
        ];

        // Alumnos de la misma materia que todavía no están cargados en esta mesa
        $estudiantesDisponibles = Estudiante::with('usuario')
            ->whereHas('materias', fn ($q) => $q->where('materias.id_materia', $mesaModel->id_materia))
            ->whereNotIn('id_usuario', $idsYaAgregados)
            ->get()
            ->map(fn ($e) => (object) [
                'id_usuario' => $e->id_usuario,
                'nombre'     => trim(($e->usuario->apellido ?? '') . ', ' . ($e->usuario->nombre ?? ''), ', '),
                'documento'  => $e->usuario->dni ?? '',
            ]);

        return view('Secretaria.finalesAgregarEst', compact('mesa', 'estudiantesDisponibles'));
    }

    /**
     * GET /finales
     * Pantalla "Fechas de finales" (con estado calculado por mesa).
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'id_ciclo',
            'id_tipo_mesa',
            'id_llamado',
            'id_materia',
            'id_carrera',
            'anio',
            'fecha_desde',
            'fecha_hasta',
            'aula_desde',
            'aula_hasta',
            'estudiantes',
        ]);

        $perPage = (int) $request->get('per_page', 15);

        $mesas = $this->mesaExamenService->getAll($filters, $perPage);

        return response()->json($mesas);
    }

    /**
     * GET /finalesHistorial/data (JSON, si algún día el historial pasa a AJAX)
     */
    public function historial(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'id_carrera'  => ['required', 'integer', 'exists:carreras,id_carrera'],
            'id_llamado'  => ['required', 'integer', 'exists:llamados,id_llamado'],
            'anio'        => ['nullable', 'integer'],
        ]);

        $mesas = $this->mesaExamenService->getHistorial($filters);

        return response()->json($mesas);
    }

    /**
     * GET /mesaFinalPerfil/{id_mesa} (JSON)
     */
    public function show(int $id): JsonResponse
    {
        $mesa = $this->mesaExamenService->find($id);

        if (!$mesa) {
            return response()->json(['message' => 'Mesa de examen no encontrada'], 404);
        }

        return response()->json($mesa);
    }

    /**
     * POST /finales
     * Pantalla "Agregar Final". Crea 1 o 2 mesas según las fechas cargadas.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_materia'                 => ['required', 'integer', 'exists:materias,id_materia'],
            'id_tipo_mesa'               => ['required', 'integer', 'exists:tipos_mesa_examen,id_tipo_mesa'],
            'id_llamado'                 => ['nullable', 'integer', 'exists:llamados,id_llamado'],
            'id_ciclo'                   => ['required', 'integer', 'exists:ciclos_lectivos,id_ciclo'],
            'aula'                       => ['nullable', 'string', 'max:255'],
            'id_usuario_administracion'  => ['required', 'integer', 'exists:administradores,id_usuario'],
            'habilitado'                 => ['sometimes', 'boolean'],

            'docente_titular'            => ['required', 'integer', 'exists:docentes,id_usuario'],
            'docente_vocal'              => ['nullable', 'integer', 'exists:docentes,id_usuario'],

            'fecha_1_desde'              => ['required', 'date'],
            'fecha_2_desde'              => ['nullable', 'date'],
        ]);

        $fechas = array_filter([
            $data['fecha_1_desde'],
            $data['fecha_2_desde'] ?? null,
        ]);

        unset($data['fecha_1_desde'], $data['fecha_2_desde']);
        $data['fechas'] = array_values($fechas);

        $mesas = $this->mesaExamenService->create($data);

        return response()->json($mesas, 201);
    }

    /**
     * PUT/PATCH /finales/{id}
     * Edición administrativa de la mesa. Distinto del botón "Modificar" de
     * "Mesa de final", que solo edita notas (ver updateNotas más abajo).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $mesa = $this->mesaExamenService->find($id);

        if (!$mesa) {
            return response()->json(['message' => 'Mesa de examen no encontrada'], 404);
        }

        $data = $request->validate([
            'fecha'                      => ['sometimes', 'required', 'date'],
            'id_tipo_mesa'               => ['sometimes', 'required', 'integer', 'exists:tipos_mesa_examen,id_tipo_mesa'],
            'id_llamado'                 => ['sometimes', 'nullable', 'integer', 'exists:llamados,id_llamado'],
            'habilitado'                 => ['sometimes', 'boolean'],
            'id_materia'                 => ['sometimes', 'required', 'integer', 'exists:materias,id_materia'],
            'aula'                       => ['nullable', 'string', 'max:255'],
            'id_usuario_administracion'  => ['sometimes', 'required', 'integer', 'exists:administradores,id_usuario'],
            'id_ciclo'                   => ['sometimes', 'required', 'integer', 'exists:ciclos_lectivos,id_ciclo'],
            'docente_titular'            => ['sometimes', 'integer', 'exists:docentes,id_usuario'],
            'docente_vocal'              => ['sometimes', 'nullable', 'integer', 'exists:docentes,id_usuario'],
        ]);

        $mesa = $this->mesaExamenService->update($mesa, $data);

        return response()->json($mesa);
    }

    /**
     * POST /finales/{id}/estudiantes
     * Pantalla "Agregar Alumno a final".
     */
    public function addEstudiantes(Request $request, int $id): JsonResponse
    {
        $mesa = $this->mesaExamenService->find($id);

        if (!$mesa) {
            return response()->json(['message' => 'Mesa de examen no encontrada'], 404);
        }

        $data = $request->validate([
            'estudiantes'    => ['required', 'array', 'min:1'],
            'estudiantes.*'  => ['integer', 'exists:estudiantes,id_usuario'],
        ]);

        $mesa = $this->mesaExamenService->addEstudiantes($mesa, $data['estudiantes']);

        return response()->json($mesa);
    }

    /**
     * POST /cargarNotaFinal/{id_mesa}
     * Pantalla "Cargar notas final" (carga inicial dedicada).
     */
    public function cargarNotas(Request $request, int $id): JsonResponse
    {
        return $this->guardarNotas($request, $id);
    }

    /**
     * PUT /mesaFinalPerfil/{id_mesa}
     * Botón "Modificar" dentro de "Mesa de final" (parte de notas).
     */
    public function updateNotas(Request $request, int $id): JsonResponse
    {
        return $this->guardarNotas($request, $id);
    }

    /**
     * Compartido por cargarNotas() y updateNotas(). El form manda
     * name="nota_{id_usuario}" (entero 1-10). 'cant' nunca se envía: se
     * calcula solo, no es editable.
     */
    protected function guardarNotas(Request $request, int $id): JsonResponse
    {
        $mesa = $this->mesaExamenService->find($id);

        if (!$mesa) {
            return response()->json(['success' => false, 'message' => 'Mesa de examen no encontrada'], 404);
        }

        $rules = [];
        foreach ($mesa->estudiantes as $estudiante) {
            $rules['nota_' . $estudiante->id_usuario] = ['nullable', 'numeric', 'min:1', 'max:10'];
        }

        $data = $request->validate($rules);

        $notas = [];
        foreach ($data as $campo => $valor) {
            if (!preg_match('/^nota_(\d+)$/', $campo, $m)) {
                continue;
            }
            $notas[(int) $m[1]] = $valor;
        }

        $this->mesaExamenService->cargarNotas($mesa, $notas);

        return response()->json([
            'success' => true,
            'message' => 'Notas guardadas correctamente.',
        ]);
    }
    
}