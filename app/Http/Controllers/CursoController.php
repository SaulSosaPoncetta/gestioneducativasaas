<?php

namespace App\Http\Controllers;

use App\Enums\Rol;
use App\Http\Requests\CursoRequest;
use App\Models\CicloLectivo;
use App\Models\Curso;
use App\Models\InstitucionNivel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Cursos y divisiones de los niveles escolares, por ciclo lectivo.
 */
class CursoController extends Controller
{
    public function index(Request $request): View
    {
        $ciclos = CicloLectivo::orderByDesc('anio')->get();
        $ciclo  = $request->filled('ciclo')
            ? $ciclos->firstWhere('id', (int) $request->ciclo)
            : CicloLectivo::actual();

        $niveles = InstitucionNivel::where('activo', true)->orderBy('orden')->get()
            ->filter(fn ($n) => $n->nivel->usaCursos())
            ->values();

        $nivel = $niveles->firstWhere('id', (int) $request->nivel) ?? $niveles->first();

        $cursos = collect();
        $totales = collect();

        if ($ciclo && $nivel) {
            $cursos = Curso::with(['nivel', 'preceptor'])
                ->where('ciclo_lectivo_id', $ciclo->id)
                ->where('institucion_nivel_id', $nivel->id)
                ->orderBy('grado')
                ->orderBy('division')
                ->get()
                ->groupBy('grado');

            $totales = Curso::where('ciclo_lectivo_id', $ciclo->id)
                ->selectRaw('institucion_nivel_id, count(*) as total')
                ->groupBy('institucion_nivel_id')
                ->pluck('total', 'institucion_nivel_id');
        }

        return view('cursos.index', compact('ciclos', 'ciclo', 'niveles', 'nivel', 'cursos', 'totales'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $ciclo = CicloLectivo::find($request->ciclo) ?? CicloLectivo::actual();
        $nivel = InstitucionNivel::find($request->nivel);

        if (! $ciclo || ! $nivel || ! $nivel->nivel->usaCursos()) {
            return redirect()->route('cursos.index')->with('error', 'Elegí el ciclo lectivo y el nivel.');
        }

        if ($ciclo->estaCerrado()) {
            return $this->cicloCerrado($ciclo, $nivel);
        }

        return view('cursos.form', [
            'curso'       => new Curso([
                'ciclo_lectivo_id'     => $ciclo->id,
                'institucion_nivel_id' => $nivel->id,
                'grado'                => (int) $request->grado ?: null,
                'turno'                => 'manana',
            ]),
            'ciclo'       => $ciclo,
            'nivel'       => $nivel,
            'preceptores' => $this->preceptores(),
        ]);
    }

    public function store(CursoRequest $request): RedirectResponse
    {
        $ciclo = CicloLectivo::findOrFail($request->ciclo_lectivo_id);
        $nivel = InstitucionNivel::findOrFail($request->institucion_nivel_id);

        if ($ciclo->estaCerrado()) {
            return $this->cicloCerrado($ciclo, $nivel);
        }

        $divisiones = $request->divisiones();

        foreach ($divisiones as $division) {
            Curso::create($request->datosCurso() + ['division' => $division]);
        }

        $grado = $nivel->nivel->etiquetaGrado((int) $request->grado);
        $texto = count($divisiones) === 1
            ? "Se creó {$grado} {$divisiones[0]}."
            : "Se crearon {$grado}: " . implode(', ', $divisiones) . '.';

        return redirect()->route('cursos.index', ['ciclo' => $ciclo->id, 'nivel' => $nivel->id])->with('ok', $texto);
    }

    public function edit(Curso $curso): View|RedirectResponse
    {
        $curso->load(['ciclo', 'nivel']);

        if ($curso->ciclo->estaCerrado()) {
            return $this->cicloCerrado($curso->ciclo, $curso->nivel);
        }

        return view('cursos.form', [
            'curso'       => $curso,
            'ciclo'       => $curso->ciclo,
            'nivel'       => $curso->nivel,
            'preceptores' => $this->preceptores(),
        ]);
    }

    public function update(CursoRequest $request, Curso $curso): RedirectResponse
    {
        $curso->load(['ciclo', 'nivel']);

        if ($curso->ciclo->estaCerrado()) {
            return $this->cicloCerrado($curso->ciclo, $curso->nivel);
        }

        $curso->update($request->datosCurso() + ['division' => $request->divisiones()[0]]);

        return redirect()->route('cursos.index', ['ciclo' => $curso->ciclo_lectivo_id, 'nivel' => $curso->institucion_nivel_id])
            ->with('ok', "Se guardaron los cambios de {$curso->nombre}.");
    }

    public function destroy(Curso $curso): RedirectResponse
    {
        $curso->load(['ciclo', 'nivel']);

        if ($curso->ciclo->estaCerrado()) {
            return $this->cicloCerrado($curso->ciclo, $curso->nivel);
        }

        $nombre = $curso->nombre;
        $curso->delete();

        return back()->with('ok', "Se eliminó {$nombre}.");
    }

    protected function preceptores()
    {
        return User::deInstitucion()
            ->where('rol', Rol::Preceptor->value)
            ->where('activo', true)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();
    }

    protected function cicloCerrado(CicloLectivo $ciclo, InstitucionNivel $nivel): RedirectResponse
    {
        return redirect()->route('cursos.index', ['ciclo' => $ciclo->id, 'nivel' => $nivel->id])
            ->with('error', "El {$ciclo->nombre} está cerrado: sus cursos no se pueden modificar.");
    }
}
