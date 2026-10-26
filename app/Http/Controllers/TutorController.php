<?php

namespace App\Http\Controllers;

use App\Http\Requests\TutorRequest;
use App\Models\Estudiante;
use App\Models\Tutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Tutores / responsables y su vínculo con los estudiantes.
 * Al agregar un tutor a un estudiante primero se busca por DNI: si ya existe
 * (ej. es el padre de un hermano) se vincula el mismo registro.
 */
class TutorController extends Controller
{
    public function index(Request $request): View
    {
        $tutores = Tutor::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = trim($request->q);
                if (preg_match('/^[\d.\s]+$/', $texto)) {
                    $q->where('documento', 'like', '%' . preg_replace('/\D/', '', $texto) . '%');
                } else {
                    foreach (preg_split('/\s+/', $texto) as $palabra) {
                        $q->where(fn ($w) => $w->where('apellido', 'like', "%{$palabra}%")->orWhere('nombre', 'like', "%{$palabra}%"));
                    }
                }
            })
            ->with(['estudiantes' => fn ($q) => $q->orderBy('apellido')])
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        return view('tutores.index', compact('tutores'));
    }

    /** Agregar tutor a un estudiante: si viene ?documento= se busca uno existente. */
    public function create(Request $request, Estudiante $estudiante): View
    {
        $documento = preg_replace('/\D/', '', (string) $request->documento);
        $tutor     = $documento ? Tutor::with('estudiantes')->where('documento', $documento)->first() : null;
        $buscado   = $documento !== '';

        if ($tutor && $estudiante->tutores()->whereKey($tutor->id)->exists()) {
            session()->now('error', "{$tutor->nombre_completo} ya está vinculado a este estudiante.");
        }

        return view('tutores.form', [
            'estudiante' => $estudiante,
            'tutor'      => $tutor ?? new Tutor(['documento' => $documento ?: null, 'domicilio' => $estudiante->domicilio]),
            'vinculo'    => (object) [
                'vinculo'               => null,
                'es_responsable'        => $estudiante->tutores()->count() === 0,
                'autorizado_retirar'    => true,
                'convive'               => true,
                'recibe_notificaciones' => true,
            ],
            'buscado'    => $buscado,
        ]);
    }

    public function store(TutorRequest $request, Estudiante $estudiante): RedirectResponse
    {
        $tutor = DB::transaction(function () use ($request, $estudiante) {
            $tutor = $request->tutorExistente();

            if ($tutor) {
                $tutor->update($request->datosTutor());
            } else {
                $tutor = Tutor::create($request->datosTutor());
            }

            if ($estudiante->tutores()->whereKey($tutor->id)->exists()) {
                $estudiante->tutores()->updateExistingPivot($tutor->id, $request->datosVinculo());
            } else {
                $estudiante->tutores()->attach($tutor->id, $request->datosVinculo());
            }

            $this->unicoResponsable($estudiante, $tutor, $request->boolean('es_responsable'));

            return $tutor;
        });

        return redirect()->route('estudiantes.show', $estudiante)
            ->with('ok', "Se vinculó a {$tutor->nombre_completo} como " . mb_strtolower(Tutor::vinculoLabel($request->vinculo)) . '.');
    }

    /** Editar datos del tutor y su vínculo con un estudiante. */
    public function editVinculo(Estudiante $estudiante, Tutor $tutor): View
    {
        $vinculado = $estudiante->tutores()->whereKey($tutor->id)->firstOrFail();

        return view('tutores.form', [
            'estudiante' => $estudiante,
            'tutor'      => $tutor->load('estudiantes'),
            'vinculo'    => $vinculado->pivot,
            'buscado'    => true,
        ]);
    }

    public function updateVinculo(TutorRequest $request, Estudiante $estudiante, Tutor $tutor): RedirectResponse
    {
        $estudiante->tutores()->whereKey($tutor->id)->firstOrFail();

        DB::transaction(function () use ($request, $estudiante, $tutor) {
            $tutor->update($request->datosTutor());
            $estudiante->tutores()->updateExistingPivot($tutor->id, $request->datosVinculo());
            $this->unicoResponsable($estudiante, $tutor, $request->boolean('es_responsable'));
        });

        return redirect()->route('estudiantes.show', $estudiante)->with('ok', "Se guardaron los datos de {$tutor->nombre_completo}.");
    }

    public function desvincular(Estudiante $estudiante, Tutor $tutor): RedirectResponse
    {
        $estudiante->tutores()->detach($tutor->id);

        return redirect()->route('estudiantes.show', $estudiante)
            ->with('ok', "Se desvinculó a {$tutor->nombre_completo}.");
    }

    /** Editar solo los datos del tutor (desde el listado de tutores). */
    public function edit(Tutor $tutor): View
    {
        return view('tutores.form', [
            'estudiante' => null,
            'tutor'      => $tutor->load('estudiantes'),
            'vinculo'    => null,
            'buscado'    => true,
        ]);
    }

    public function update(TutorRequest $request, Tutor $tutor): RedirectResponse
    {
        $tutor->update($request->datosTutor());

        return redirect()->route('tutores.index')->with('ok', "Se guardaron los datos de {$tutor->nombre_completo}.");
    }

    public function destroy(Tutor $tutor): RedirectResponse
    {
        if ($tutor->estudiantes()->exists()) {
            return back()->with('error', 'El tutor tiene estudiantes vinculados: desvinculalo primero.');
        }

        $nombre = $tutor->nombre_completo;
        $tutor->delete();

        return redirect()->route('tutores.index')->with('ok', "Se eliminó a {$nombre}.");
    }

    /** Solo un responsable principal por estudiante. */
    protected function unicoResponsable(Estudiante $estudiante, Tutor $tutor, bool $esResponsable): void
    {
        if ($esResponsable) {
            DB::table('estudiante_tutor')
                ->where('estudiante_id', $estudiante->id)
                ->where('tutor_id', '!=', $tutor->id)
                ->update(['es_responsable' => false]);
        }
    }
}
