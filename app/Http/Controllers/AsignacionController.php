<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsignacionRequest;
use App\Models\Asignacion;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\EspacioCurricular;
use App\Models\InstitucionNivel;
use App\Models\PlanEstudio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Asignaciones docentes: quién dicta cada espacio curricular en cada curso
 * (o comisión) del ciclo. El listado muestra los espacios sin cubrir.
 */
class AsignacionController extends Controller
{
    public function index(Request $request): View
    {
        $ciclos  = CicloLectivo::orderByDesc('anio')->get();
        $ciclo   = $ciclos->firstWhere('id', (int) $request->ciclo) ?? CicloLectivo::actual();
        $niveles = InstitucionNivel::where('activo', true)->orderBy('orden')->get();
        $nivel   = $niveles->firstWhere('id', (int) $request->nivel) ?? $niveles->first();

        $grupos = collect();       // cursos (escolar) o carreras (superior)
        $porClave = collect();     // asignaciones activas agrupadas por "grupo-espacio"
        $totales = ['espacios' => 0, 'cubiertos' => 0];

        if ($ciclo && $nivel) {
            if ($nivel->nivel->usaCursos()) {
                $grupos = Curso::with(['nivel', 'plan.espacios'])
                    ->where('ciclo_lectivo_id', $ciclo->id)
                    ->where('institucion_nivel_id', $nivel->id)
                    ->when($request->filled('curso'), fn ($q) => $q->whereKey($request->curso))
                    ->orderBy('grado')->orderBy('division')
                    ->get();

                $porClave = Asignacion::with('docente')->activas()
                    ->where('ciclo_lectivo_id', $ciclo->id)
                    ->whereIn('curso_id', $grupos->pluck('id'))
                    ->get()
                    ->groupBy(fn ($a) => "{$a->curso_id}-{$a->espacio_curricular_id}");

                foreach ($grupos as $curso) {
                    foreach ($curso->plan?->espacios->where('grado', $curso->grado) ?? [] as $e) {
                        $totales['espacios']++;
                        $totales['cubiertos'] += $porClave->has("{$curso->id}-{$e->id}") ? 1 : 0;
                    }
                }
            } else {
                $grupos = PlanEstudio::with('espacios')
                    ->where('institucion_nivel_id', $nivel->id)
                    ->where('tipo', 'carrera')
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get();

                $porClave = Asignacion::with('docente')->activas()
                    ->where('ciclo_lectivo_id', $ciclo->id)
                    ->whereNull('curso_id')
                    ->whereIn('espacio_curricular_id', $grupos->flatMap->espacios->pluck('id'))
                    ->get()
                    ->groupBy(fn ($a) => "p-{$a->espacio_curricular_id}");

                foreach ($grupos->flatMap->espacios as $e) {
                    $totales['espacios']++;
                    $totales['cubiertos'] += $porClave->has("p-{$e->id}") ? 1 : 0;
                }
            }
        }

        $cursosFiltro = ($ciclo && $nivel?->nivel->usaCursos())
            ? Curso::with('nivel')->where('ciclo_lectivo_id', $ciclo->id)->where('institucion_nivel_id', $nivel->id)
                ->orderBy('grado')->orderBy('division')->get()
            : collect();

        return view('asignaciones.index', compact('ciclos', 'ciclo', 'niveles', 'nivel', 'grupos', 'porClave', 'totales', 'cursosFiltro'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $ciclo = CicloLectivo::find($request->ciclo) ?? CicloLectivo::actual();

        if (! $ciclo || $ciclo->estaCerrado()) {
            return redirect()->route('asignaciones.index')->with('error', 'Necesitás un ciclo lectivo abierto para asignar docentes.');
        }

        $curso = $request->filled('curso')
            ? Curso::with(['nivel', 'plan.espacios'])->where('ciclo_lectivo_id', $ciclo->id)->find($request->curso)
            : null;
        $plan = ! $curso && $request->filled('plan')
            ? PlanEstudio::with(['nivel', 'espacios'])->where('tipo', 'carrera')->find($request->plan)
            : null;

        $espacios = $curso
            ? ($curso->plan?->espacios->where('grado', $curso->grado) ?? collect())
            : ($plan?->espacios ?? collect());

        // Para elegir el curso / la carrera si todavía no se eligió.
        $cursos = Curso::with('nivel')->where('ciclo_lectivo_id', $ciclo->id)
            ->orderBy('institucion_nivel_id')->orderBy('grado')->orderBy('division')->get();
        $carreras = PlanEstudio::with('nivel')->where('tipo', 'carrera')->where('activo', true)->orderBy('nombre')->get();

        return view('asignaciones.create', [
            'ciclo'       => $ciclo,
            'curso'       => $curso,
            'plan'        => $plan,
            'espacios'    => $espacios,
            'cursos'      => $cursos,
            'carreras'    => $carreras,
            'docentes'    => Docente::activos()->orderBy('apellido')->orderBy('nombre')->get(),
            'docenteId'   => (int) $request->docente,
            'espacioId'   => (int) $request->espacio,
            'ocupados'    => $this->ocupados($ciclo, $curso, $plan),
        ]);
    }

    public function store(AsignacionRequest $request): RedirectResponse
    {
        $curso    = $request->curso();
        $espacios = EspacioCurricular::whereIn('id', $request->espacios)->get();
        $creadas  = 0;
        $repetidas = [];

        foreach ($espacios as $espacio) {
            $existe = Asignacion::activas()
                ->where('docente_id', $request->docente_id)
                ->where('ciclo_lectivo_id', $request->ciclo_lectivo_id)
                ->where('espacio_curricular_id', $espacio->id)
                ->when($curso, fn ($q) => $q->where('curso_id', $curso->id), fn ($q) => $q->whereNull('curso_id')->where('comision', $request->comision))
                ->exists();

            if ($existe) {
                $repetidas[] = $espacio->nombre;
                continue;
            }

            Asignacion::create([
                'docente_id'            => $request->docente_id,
                'ciclo_lectivo_id'      => $request->ciclo_lectivo_id,
                'espacio_curricular_id' => $espacio->id,
                'curso_id'              => $curso?->id,
                'comision'              => $request->comision,
                'situacion'             => $request->situacion,
                'horas'                 => $espacio->horas_semanales,
                'fecha_desde'           => $request->fecha_desde,
                'fecha_hasta'           => $request->fecha_hasta,
                'observaciones'         => $request->observaciones,
            ]);
            $creadas++;
        }

        $docente = Docente::find($request->docente_id);
        $texto = $creadas === 1 ? 'Se asignó 1 espacio' : "Se asignaron {$creadas} espacios";
        $texto .= " a {$docente->nombre_completo}.";
        if ($repetidas) {
            $texto .= ' Ya tenía asignado: ' . implode(', ', $repetidas) . '.';
        }

        $nivelId = $curso?->institucion_nivel_id ?? PlanEstudio::find($request->plan_estudio_id)?->institucion_nivel_id;

        return redirect()->route('asignaciones.index', array_filter([
            'ciclo' => $request->ciclo_lectivo_id,
            'nivel' => $nivelId,
        ]))->with('ok', $texto);
    }

    public function edit(Asignacion $asignacion): View
    {
        $asignacion->load(['docente', 'ciclo', 'espacio.plan', 'curso.nivel']);

        return view('asignaciones.edit', compact('asignacion'));
    }

    public function update(Request $request, Asignacion $asignacion): RedirectResponse
    {
        $datos = $request->validate([
            'situacion'     => ['required', Rule::in(array_keys(Asignacion::SITUACIONES))],
            'horas'         => ['nullable', 'integer', 'between:1,40'],
            'comision'      => [$asignacion->curso_id ? 'exclude' : 'required', 'string', 'max:30'],
            'fecha_desde'   => ['required', 'date'],
            'fecha_hasta'   => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'estado'        => ['required', Rule::in([Asignacion::ACTIVA, Asignacion::FINALIZADA])],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ], [], ['situacion' => 'situación de revista', 'fecha_desde' => 'desde', 'fecha_hasta' => 'hasta', 'comision' => 'comisión']);

        // Al finalizar sin fecha de fin, se toma hoy.
        if ($datos['estado'] === Asignacion::FINALIZADA && empty($datos['fecha_hasta'])) {
            $datos['fecha_hasta'] = today()->toDateString();
        }

        $asignacion->update($datos);

        return redirect()->to($this->volver($request, route('docentes.show', $asignacion->docente_id)))
            ->with('ok', 'Se guardó la asignación.');
    }

    public function destroy(Request $request, Asignacion $asignacion): RedirectResponse
    {
        $asignacion->delete();

        return redirect()->to($this->volver($request, route('asignaciones.index')))->with('ok', 'Se eliminó la asignación.');
    }

    /** Vuelve a la pantalla de origen (solo URLs del propio sistema). */
    protected function volver(Request $request, string $porDefecto): string
    {
        $url = (string) $request->input('volver');

        return $url !== '' && str_starts_with($url, url('/')) ? $url : $porDefecto;
    }

    /** Docentes que ya tienen cada espacio en ese curso/carrera (para avisar en el formulario). */
    protected function ocupados(CicloLectivo $ciclo, ?Curso $curso, ?PlanEstudio $plan)
    {
        if (! $curso && ! $plan) {
            return collect();
        }

        return Asignacion::with('docente')->activas()
            ->where('ciclo_lectivo_id', $ciclo->id)
            ->when($curso, fn ($q) => $q->where('curso_id', $curso->id), fn ($q) => $q->whereNull('curso_id')
                ->whereIn('espacio_curricular_id', $plan->espacios->pluck('id')))
            ->get()
            ->groupBy('espacio_curricular_id');
    }
}
