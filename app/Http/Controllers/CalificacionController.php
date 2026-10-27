<?php

namespace App\Http\Controllers;

use App\Enums\EscalaCalificacion;
use App\Models\Calificacion;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\EspacioCurricular;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\InstitucionNivel;
use App\Models\PeriodoEvaluacion;
use App\Models\PlanEstudio;
use App\Services\AsistenciaService;
use App\Services\CalificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Calificaciones: planilla por curso y período, boletín del estudiante,
 * corrección manual (secretaría) y configuración de escalas por nivel.
 * Las notas las cargan los docentes en GestiónAula y llegan por la API.
 */
class CalificacionController extends Controller
{
    public function __construct(protected CalificacionService $servicio)
    {
    }

    public function index(Request $request): View
    {
        $ciclos  = CicloLectivo::orderByDesc('anio')->get();
        $ciclo   = $ciclos->firstWhere('id', (int) $request->ciclo) ?? CicloLectivo::actual();
        $niveles = InstitucionNivel::where('activo', true)->orderBy('orden')->get();
        $nivel   = $niveles->firstWhere('id', (int) $request->nivel) ?? $niveles->first();

        $datos = ['ciclos' => $ciclos, 'ciclo' => $ciclo, 'niveles' => $niveles, 'nivel' => $nivel,
                  'grupos' => collect(), 'grupo' => null, 'anio' => null, 'periodos' => collect(), 'periodo' => null,
                  'estudiantes' => collect(), 'espacios' => collect(), 'notas' => collect()];

        if (! $ciclo || ! $nivel) {
            return view('calificaciones.index', $datos);
        }

        $periodos = PeriodoEvaluacion::where('ciclo_lectivo_id', $ciclo->id)->where('institucion_nivel_id', $nivel->id)->orderBy('orden')->get();
        $periodo  = $periodos->firstWhere('id', (int) $request->periodo)
            ?? $periodos->first(fn ($p) => $p->estaVigente())
            ?? $periodos->first();

        if ($nivel->nivel->usaCursos()) {
            $grupos = Curso::with(['nivel', 'plan'])->where('ciclo_lectivo_id', $ciclo->id)->where('institucion_nivel_id', $nivel->id)
                ->orderBy('grado')->orderBy('division')->get();
            $grupo = $grupos->firstWhere('id', (int) $request->curso) ?? $grupos->first();

            $estudiantes = $grupo ? app(AsistenciaService::class)->inscriptos($grupo, today()->min($ciclo->fecha_fin)) : collect();
            $espacios = $grupo?->plan_estudio_id
                ? EspacioCurricular::where('plan_estudio_id', $grupo->plan_estudio_id)->where('grado', $grupo->grado)->orderBy('orden')->orderBy('id')->get()
                : collect();
            $anio = null;
        } else {
            $grupos = PlanEstudio::where('institucion_nivel_id', $nivel->id)->where('tipo', 'carrera')->orderBy('nombre')->get();
            $grupo  = $grupos->firstWhere('id', (int) $request->plan) ?? $grupos->first();
            $anio   = $grupo ? min(max((int) $request->anio ?: 1, $grupo->grado_desde), $grupo->grado_hasta) : null;

            $estudiantes = $grupo
                ? Inscripcion::with('estudiante')->where('ciclo_lectivo_id', $ciclo->id)->where('plan_estudio_id', $grupo->id)
                    ->where('estado', Inscripcion::ACTIVA)->get()->pluck('estudiante')
                    ->sortBy(fn ($e) => mb_strtolower(Str::ascii($e->apellido . ' ' . $e->nombre)))->values()
                : collect();
            $espacios = $grupo ? EspacioCurricular::where('plan_estudio_id', $grupo->id)->where('grado', $anio)->orderBy('orden')->get() : collect();
        }

        $notas = $periodo
            ? Calificacion::where('periodo_evaluacion_id', $periodo->id)
                ->whereIn('estudiante_id', $estudiantes->pluck('id'))
                ->whereIn('espacio_curricular_id', $espacios->pluck('id'))
                ->get()
                ->keyBy(fn ($c) => "{$c->estudiante_id}-{$c->espacio_curricular_id}")
            : collect();

        return view('calificaciones.index', array_merge($datos, compact('grupos', 'grupo', 'anio', 'periodos', 'periodo', 'estudiantes', 'espacios', 'notas')));
    }

    /** Formulario para cargar o corregir una nota (secretaría). */
    public function cargar(Request $request): View|RedirectResponse
    {
        $estudiante = Estudiante::findOrFail($request->estudiante);
        $espacio    = EspacioCurricular::with('plan')->findOrFail($request->espacio);
        $periodo    = PeriodoEvaluacion::with(['nivel', 'ciclo'])->findOrFail($request->periodo);

        $calificacion = Calificacion::with(['docente', 'modificador'])
            ->where('estudiante_id', $estudiante->id)
            ->where('espacio_curricular_id', $espacio->id)
            ->where('periodo_evaluacion_id', $periodo->id)
            ->first();

        return view('calificaciones.cargar', [
            'estudiante'   => $estudiante,
            'espacio'      => $espacio,
            'periodo'      => $periodo,
            'calificacion' => $calificacion,
            'escala'       => $periodo->nivel->escalaCalificacion(),
            'volver'       => $this->volver($request),
        ]);
    }

    public function guardar(Request $request): RedirectResponse
    {
        $estudiante = Estudiante::findOrFail($request->estudiante);
        $espacio    = EspacioCurricular::findOrFail($request->espacio);
        $periodo    = PeriodoEvaluacion::with(['nivel', 'ciclo'])->findOrFail($request->periodo);
        $escala     = $periodo->nivel->escalaCalificacion();

        if ($periodo->ciclo->estaCerrado()) {
            return back()->with('error', 'El ciclo lectivo está cerrado.');
        }

        $request->validate([
            'nota'        => [$escala === EscalaCalificacion::Informe ? 'nullable' : 'required', 'string', 'max:20'],
            'informe'     => [$escala === EscalaCalificacion::Informe ? 'required' : 'nullable', 'string', 'max:5000'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        $valor = $numerico = null;
        if ($escala !== EscalaCalificacion::Informe) {
            $normal = $escala->normalizar($request->nota);
            if (! $normal) {
                return back()->withInput()->withErrors(['nota' => 'La nota no es válida para la escala (' . $escala->label() . ').']);
            }
            [$valor, $numerico] = $normal;
        }

        $curso = Inscripcion::where('estudiante_id', $estudiante->id)->where('ciclo_lectivo_id', $periodo->ciclo_lectivo_id)
            ->where('estado', Inscripcion::ACTIVA)->value('curso_id');

        Calificacion::updateOrCreate(
            ['estudiante_id' => $estudiante->id, 'espacio_curricular_id' => $espacio->id, 'periodo_evaluacion_id' => $periodo->id],
            [
                'ciclo_lectivo_id' => $periodo->ciclo_lectivo_id,
                'curso_id'         => $curso,
                'valor'            => $valor,
                'valor_numerico'   => $numerico,
                'informe'          => $escala === EscalaCalificacion::Informe ? $request->informe : null,
                'observacion'      => $request->observacion,
                'origen'           => 'manual',
                'modificado_por'   => $request->user()->id,
            ]
        );

        return redirect()->to($this->volver($request))
            ->with('ok', "Se guardó la nota de {$estudiante->nombre_completo} en {$espacio->nombre} ({$periodo->nombre}).");
    }

    /** Devuelve la nota a GestiónAula: borra la corrección manual. */
    public function eliminar(Request $request, Calificacion $calificacion): RedirectResponse
    {
        $calificacion->delete();

        return redirect()->to($this->volver($request))->with('ok', 'Se eliminó la nota. GestiónAula puede volver a enviarla.');
    }

    public function boletin(Request $request, Estudiante $estudiante): View
    {
        $ciclos = CicloLectivo::whereHas('periodos')->orderByDesc('anio')->get();
        $ciclo  = $ciclos->firstWhere('id', (int) $request->ciclo) ?? CicloLectivo::actual();

        return view('calificaciones.boletin', [
            'estudiante' => $estudiante,
            'ciclos'     => $ciclos,
            'ciclo'      => $ciclo,
            'datos'      => $ciclo ? $this->servicio->boletin($estudiante, $ciclo) : null,
        ]);
    }

    public function configuracion(): View
    {
        return view('calificaciones.configuracion', [
            'niveles' => InstitucionNivel::where('activo', true)->orderBy('orden')->get(),
        ]);
    }

    public function guardarConfiguracion(Request $request): RedirectResponse
    {
        $request->validate([
            'niveles'                   => ['required', 'array'],
            'niveles.*.escala'          => ['required', Rule::enum(EscalaCalificacion::class)],
            'niveles.*.nota_aprobacion' => ['nullable', 'numeric', 'between:1,10'],
            'niveles.*.aprueba_desde'   => ['nullable', Rule::in(array_keys(EscalaCalificacion::CONCEPTOS))],
        ], [], ['niveles.*.nota_aprobacion' => 'nota de aprobación']);

        foreach ($request->niveles as $id => $datos) {
            InstitucionNivel::whereKey($id)->first()?->update([
                'escala'          => $datos['escala'],
                'nota_aprobacion' => $datos['nota_aprobacion'] ?? null,
                'aprueba_desde'   => $datos['aprueba_desde'] ?? null,
            ]);
        }

        return redirect()->route('calificaciones.configuracion')->with('ok', 'Se guardó la configuración de calificaciones.');
    }

    protected function volver(Request $request): string
    {
        $url = (string) $request->input('volver');

        return $url !== '' && str_starts_with($url, url('/')) ? $url : route('calificaciones.index');
    }
}
