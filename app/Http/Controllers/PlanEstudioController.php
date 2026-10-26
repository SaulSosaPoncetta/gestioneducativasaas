<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlanEstudioRequest;
use App\Models\InstitucionNivel;
use App\Models\PlanEstudio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Planes de estudio de todos los niveles: plan general (jardín, primaria),
 * ciclo básico / orientaciones / especialidades (secundaria, técnica)
 * y carreras (terciario, universitario).
 */
class PlanEstudioController extends Controller
{
    public function index(): View
    {
        $niveles = InstitucionNivel::where('activo', true)->orderBy('orden')->get();

        $planes = PlanEstudio::with('nivel')
            ->withCount('espacios')
            ->withSum('espacios', 'horas_semanales')
            ->orderBy('grado_desde')
            ->orderBy('nombre')
            ->get()
            ->groupBy('institucion_nivel_id');

        return view('planes.index', compact('niveles', 'planes'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $nivel = InstitucionNivel::where('activo', true)->find($request->nivel);

        if (! $nivel) {
            return redirect()->route('planes.index')->with('error', 'Elegí el nivel del plan.');
        }

        $grados = $nivel->nivel->grados($nivel->duracion);

        return view('planes.form', [
            'plan' => new PlanEstudio([
                'institucion_nivel_id' => $nivel->id,
                'tipo'                 => array_key_first($nivel->nivel->tiposPlan()),
                'grado_desde'          => $nivel->nivel->usaCarreras() ? 1 : $grados[0],
                'grado_hasta'          => $nivel->nivel->usaCarreras() ? 4 : end($grados),
                'modalidad'            => $nivel->nivel->usaCarreras() ? 'presencial' : null,
                'activo'               => true,
            ]),
            'nivel' => $nivel,
        ]);
    }

    public function store(PlanEstudioRequest $request): RedirectResponse
    {
        $plan = PlanEstudio::create($request->datosPlan());

        return redirect()->route('planes.show', $plan)
            ->with('ok', "Se creó {$plan->nombre}. Ahora cargá sus espacios curriculares.");
    }

    public function show(PlanEstudio $plan): View
    {
        $plan->load(['nivel', 'espacios.correlativas']);

        return view('planes.show', [
            'plan'    => $plan,
            'nivel'   => $plan->nivel,
            'porAnio' => $plan->espacios->groupBy('grado'),
        ]);
    }

    public function edit(PlanEstudio $plan): View
    {
        return view('planes.form', ['plan' => $plan, 'nivel' => $plan->nivel]);
    }

    public function update(PlanEstudioRequest $request, PlanEstudio $plan): RedirectResponse
    {
        $plan->update($request->datosPlan());

        return redirect()->route('planes.show', $plan)->with('ok', 'Se guardaron los datos del plan.');
    }

    public function destroy(PlanEstudio $plan): RedirectResponse
    {
        $nombre = $plan->nombre;
        $plan->delete();

        return redirect()->route('planes.index')->with('ok', "Se eliminó {$nombre}.");
    }

    /** Copia el plan con sus espacios y correlatividades (para una nueva versión del plan). */
    public function duplicar(PlanEstudio $plan): RedirectResponse
    {
        $copia = DB::transaction(function () use ($plan) {
            $copia = $plan->replicate();
            $copia->nombre = mb_substr($plan->nombre . ' (copia)', 0, 150);
            $copia->activo = false;
            $copia->save();

            $mapa = [];
            foreach ($plan->espacios as $espacio) {
                $nuevo = $espacio->replicate();
                $nuevo->plan_estudio_id = $copia->id;
                $nuevo->save();
                $mapa[$espacio->id] = $nuevo->id;
            }

            $filas = DB::table('correlatividades')->whereIn('espacio_curricular_id', array_keys($mapa))->get();
            foreach ($filas as $fila) {
                DB::table('correlatividades')->insert([
                    'espacio_curricular_id' => $mapa[$fila->espacio_curricular_id],
                    'requiere_id'           => $mapa[$fila->requiere_id],
                    'tipo'                  => $fila->tipo,
                ]);
            }

            return $copia;
        });

        return redirect()->route('planes.edit', $copia)
            ->with('ok', 'Se duplicó el plan (queda inactivo). Cambiale el nombre y la resolución.');
    }
}
