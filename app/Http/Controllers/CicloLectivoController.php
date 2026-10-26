<?php

namespace App\Http\Controllers;

use App\Enums\EsquemaEvaluacion;
use App\Http\Requests\CicloLectivoRequest;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\InstitucionNivel;
use App\Models\PeriodoEvaluacion;
use App\Services\CicloLectivoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CicloLectivoController extends Controller
{
    public function __construct(protected CicloLectivoService $servicio)
    {
    }

    public function index(): View
    {
        $ciclos = CicloLectivo::withCount('cursos')
            ->orderByDesc('anio')
            ->get();

        return view('ciclos.index', compact('ciclos'));
    }

    public function create(): View
    {
        $ultimo = CicloLectivo::orderByDesc('anio')->first();
        $anio   = $ultimo ? max($ultimo->anio + 1, (int) date('Y')) : (int) date('Y');

        $ciclo = new CicloLectivo([
            'anio'         => $anio,
            'nombre'       => "Ciclo lectivo {$anio}",
            'fecha_inicio' => Carbon::create($anio, 3, 2),
            'fecha_fin'    => Carbon::create($anio, 12, 18),
        ]);

        // Cursos del ciclo anterior por nivel, para ofrecer copiarlos.
        $cursosAnteriores = $ultimo
            ? Curso::where('ciclo_lectivo_id', $ultimo->id)
                ->selectRaw('institucion_nivel_id, count(*) as total')
                ->groupBy('institucion_nivel_id')
                ->pluck('total', 'institucion_nivel_id')
            : collect();

        return view('ciclos.create', [
            'ciclo'            => $ciclo,
            'niveles'          => InstitucionNivel::where('activo', true)->orderBy('orden')->get(),
            'esquemas'         => EsquemaEvaluacion::opciones(),
            'anterior'         => $ultimo,
            'cursosAnteriores' => $cursosAnteriores,
        ]);
    }

    public function store(CicloLectivoRequest $request): RedirectResponse
    {
        $ciclo = $this->servicio->crear($request->datosCiclo(), $request->input('niveles', []));

        return redirect()->route('ciclos.show', $ciclo)
            ->with('ok', "Se creó el {$ciclo->nombre}. Revisá las fechas de los períodos.");
    }

    public function show(CicloLectivo $ciclo): View
    {
        $periodos = $ciclo->periodos()->get()->groupBy('institucion_nivel_id');
        $cursos   = $ciclo->cursos()
            ->selectRaw('institucion_nivel_id, count(*) as total')
            ->groupBy('institucion_nivel_id')
            ->pluck('total', 'institucion_nivel_id');

        // Niveles activos + los que tengan períodos en este ciclo (aunque hoy estén inactivos).
        $niveles = InstitucionNivel::where('activo', true)
            ->orWhereIn('id', $periodos->keys())
            ->orderBy('orden')
            ->get();

        return view('ciclos.show', [
            'ciclo'    => $ciclo,
            'niveles'  => $niveles,
            'periodos' => $periodos,
            'cursos'   => $cursos,
            'esquemas' => EsquemaEvaluacion::opciones(),
            'servicio' => $this->servicio,
        ]);
    }

    public function edit(CicloLectivo $ciclo): View
    {
        return view('ciclos.edit', compact('ciclo'));
    }

    public function update(CicloLectivoRequest $request, CicloLectivo $ciclo): RedirectResponse
    {
        $ciclo->update($request->datosCiclo());

        return redirect()->route('ciclos.show', $ciclo)->with('ok', 'Se guardaron los datos del ciclo.');
    }

    public function destroy(CicloLectivo $ciclo): RedirectResponse
    {
        if ($ciclo->estado !== CicloLectivo::PLANIFICACION) {
            return back()->with('error', 'Solo se puede eliminar un ciclo en planificación.');
        }

        $nombre = $ciclo->nombre;
        $ciclo->delete();

        return redirect()->route('ciclos.index')->with('ok', "Se eliminó el {$nombre}.");
    }

    public function estado(Request $request, CicloLectivo $ciclo): RedirectResponse
    {
        $request->validate(['estado' => ['required', Rule::in(array_keys(CicloLectivo::ESTADOS))]]);

        $this->servicio->cambiarEstado($ciclo, $request->estado);

        return back()->with('ok', "El {$ciclo->nombre} quedó " . mb_strtolower($ciclo->estado_label) . '.');
    }

    /** Guarda nombres, fechas y cierre de los períodos de un nivel. */
    public function periodos(Request $request, CicloLectivo $ciclo): RedirectResponse
    {
        $datos = $request->validate([
            'periodos'                => ['required', 'array'],
            'periodos.*.nombre'       => ['required', 'string', 'max:60'],
            'periodos.*.fecha_inicio' => ['required', 'date'],
            'periodos.*.fecha_fin'    => ['required', 'date'],
            'periodos.*.cerrado'      => ['nullable', 'boolean'],
        ], [], [
            'periodos.*.nombre'       => 'nombre del período',
            'periodos.*.fecha_inicio' => 'inicio del período',
            'periodos.*.fecha_fin'    => 'fin del período',
        ]);

        $periodos = PeriodoEvaluacion::where('ciclo_lectivo_id', $ciclo->id)
            ->whereIn('id', array_keys($datos['periodos']))
            ->get()
            ->keyBy('id');

        foreach ($datos['periodos'] as $id => $p) {
            if (Carbon::parse($p['fecha_fin'])->lt(Carbon::parse($p['fecha_inicio']))) {
                return back()->withInput()->with('error', "En \"{$p['nombre']}\" la fecha de fin es anterior a la de inicio.");
            }
        }

        foreach ($datos['periodos'] as $id => $p) {
            $periodos->get($id)?->update([
                'nombre'       => $p['nombre'],
                'fecha_inicio' => $p['fecha_inicio'],
                'fecha_fin'    => $p['fecha_fin'],
                'cerrado'      => (bool) ($p['cerrado'] ?? false),
            ]);
        }

        return back()->with('ok', 'Se guardaron los períodos.');
    }

    /** Vuelve a generar los períodos de un nivel con otro esquema. */
    public function esquema(Request $request, CicloLectivo $ciclo, InstitucionNivel $nivel): RedirectResponse
    {
        $request->validate([
            'esquema'         => ['required', Rule::enum(EsquemaEvaluacion::class)],
            'intensificacion' => ['nullable', 'boolean'],
        ]);

        $this->servicio->generarPeriodos(
            $ciclo,
            $nivel,
            EsquemaEvaluacion::from($request->esquema),
            $request->boolean('intensificacion'),
        );

        return back()->with('ok', "Se regeneraron los períodos de {$nivel->nivel->label()}.");
    }
}
