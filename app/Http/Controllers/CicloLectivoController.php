<?php
namespace App\Http\Controllers;
use App\Models\CicloLectivo;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCicloLectivoRequest;
use App\Http\Requests\UpdateCicloLectivoRequest;

class CicloLectivoController extends Controller
{
    public function index()
    {
        $ciclos = CicloLectivo::orderBy('fecha_inicio', 'desc')->get();

        return view('Secretaria.cicloLectivoSec', compact('ciclos'));
    }
    
    public function show(string $id)
    {
        $ciclo = CicloLectivo::findOrFail($id);

        return view(
            'Secretaria.cicloLectivoPerfil',
            compact('ciclo')
        );
    }

    public function create()
    {
        return view('Secretaria.cicloLectivoAgregar');
    }

    public function store(StoreCicloLectivoRequest $request)
    {
        $ciclo = CicloLectivo::create([
            'anio' => $request->anio,
            'descripcion' => "Ciclo Lectivo " . $request->anio,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'inicio_matriculacion' => $request->inicio_matriculacion,
            'fin_matriculacion' => $request->fin_matriculacion,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ciclo lectivo agregado correctamente.',
            'data' => $ciclo
        ], 201);
    }

    public function update(UpdateCicloLectivoRequest $request, string $id)
    {
        $ciclo = CicloLectivo::findOrFail($id);

        $ciclo->update([
            'anio' => $request->anio,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'inicio_matriculacion' => $request->inicio_matriculacion,
            'fin_matriculacion' => $request->fin_matriculacion,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ciclo lectivo actualizado correctamente.',
            'data' => $ciclo]);
    }   
}
