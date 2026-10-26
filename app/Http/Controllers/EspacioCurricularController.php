<?php

namespace App\Http\Controllers;

use App\Http\Requests\EspacioCurricularRequest;
use App\Models\EspacioCurricular;
use App\Models\PlanEstudio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Espacios curriculares de un plan (áreas, materias, talleres...) y sus
 * correlatividades en las carreras de nivel superior.
 */
class EspacioCurricularController extends Controller
{
    public function create(Request $request, PlanEstudio $plan): View
    {
        $espacio = new EspacioCurricular([
            'plan_estudio_id' => $plan->id,
            'grado'           => in_array((int) $request->grado, $plan->grados(), true) ? (int) $request->grado : $plan->grado_desde,
            'regimen'         => 'anual',
            'formato'         => in_array($plan->nivel->nivel->value, ['inicial', 'primario'], true) ? 'area' : 'materia',
        ]);

        return $this->formulario($plan, $espacio);
    }

    public function store(EspacioCurricularRequest $request, PlanEstudio $plan): RedirectResponse
    {
        $espacio = DB::transaction(function () use ($request, $plan) {
            $espacio = $plan->espacios()->create($request->datosEspacio());
            $this->guardarCorrelativas($espacio, $request->correlativas());

            return $espacio;
        });

        $destino = $request->boolean('otro')
            ? redirect()->route('planes.espacios.create', ['plan' => $plan, 'grado' => $espacio->grado])
            : redirect()->route('planes.show', $plan);

        return $destino->with('ok', "Se agregó {$espacio->nombre}.");
    }

    public function edit(EspacioCurricular $espacio): View
    {
        $espacio->load(['plan.nivel', 'correlativas']);

        return $this->formulario($espacio->plan, $espacio);
    }

    public function update(EspacioCurricularRequest $request, EspacioCurricular $espacio): RedirectResponse
    {
        DB::transaction(function () use ($request, $espacio) {
            $espacio->update($request->datosEspacio());
            $this->guardarCorrelativas($espacio, $request->correlativas());

            // Si cambió de año, las que dependen de él y ya no quedan "después" se quitan.
            $espacio->habilitaA()->get()
                ->reject(fn ($dependiente) => $espacio->esAnteriorA($dependiente))
                ->each(fn ($dependiente) => $espacio->habilitaA()->detach($dependiente->id));
        });

        return redirect()->route('planes.show', $espacio->plan_estudio_id)->with('ok', "Se guardaron los cambios de {$espacio->nombre}.");
    }

    public function destroy(EspacioCurricular $espacio): RedirectResponse
    {
        $nombre = $espacio->nombre;
        $plan   = $espacio->plan_estudio_id;
        $espacio->delete();

        return redirect()->route('planes.show', $plan)->with('ok', "Se eliminó {$nombre}.");
    }

    protected function formulario(PlanEstudio $plan, EspacioCurricular $espacio): View
    {
        $plan->loadMissing('nivel');

        // Candidatas a correlativas: todo el plan menos el propio espacio
        // (la vista filtra por "anterior" según el año/régimen elegido).
        $candidatas = $plan->esCarrera()
            ? $plan->espacios()->when($espacio->exists, fn ($q) => $q->whereKeyNot($espacio->id))->get()
            : collect();

        $elegidas = $espacio->exists
            ? $espacio->correlativas->groupBy('pivot.tipo')->map->pluck('id')
            : collect();

        return view('espacios.form', compact('plan', 'espacio', 'candidatas', 'elegidas'));
    }

    /** @param array<string, int[]> $porTipo */
    protected function guardarCorrelativas(EspacioCurricular $espacio, array $porTipo): void
    {
        DB::table('correlatividades')->where('espacio_curricular_id', $espacio->id)->delete();

        foreach ($porTipo as $tipo => $ids) {
            foreach ($ids as $id) {
                DB::table('correlatividades')->insert([
                    'espacio_curricular_id' => $espacio->id,
                    'requiere_id'           => $id,
                    'tipo'                  => $tipo,
                ]);
            }
        }
    }
}
