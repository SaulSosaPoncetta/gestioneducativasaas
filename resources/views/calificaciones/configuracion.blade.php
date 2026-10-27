@extends('layouts.app')

@section('titulo', 'Escalas de calificación')

@section('contenido')

<div class="mb-3">
    <h1 class="h3 mb-0">Escalas de calificación</h1>
    <p class="text-muted mb-0">Cómo se califica en cada nivel y con qué nota se aprueba. GestiónAula envía las notas en esta escala.</p>
</div>

<form method="POST" action="{{ route('calificaciones.configuracion.guardar') }}">
    @csrf @method('PUT')
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nivel</th>
                        <th style="width: 280px">Escala</th>
                        <th style="width: 170px">Aprueba con (numérica)</th>
                        <th style="width: 190px">Aprueba desde (conceptual)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($niveles as $n)
                        <tr>
                            <td><i class="bi {{ $n->nivel->icono() }} text-primary me-1"></i> {{ $n->nombre }}</td>
                            <td>
                                <select name="niveles[{{ $n->id }}][escala]" class="form-select form-select-sm">
                                    @foreach (\App\Enums\EscalaCalificacion::opciones() as $v => $t)
                                        <option value="{{ $v }}" @selected(old("niveles.{$n->id}.escala", $n->escalaCalificacion()->value) === $v)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.5" min="1" max="10" name="niveles[{{ $n->id }}][nota_aprobacion]"
                                       value="{{ old("niveles.{$n->id}.nota_aprobacion", $n->notaAprobacion()) }}"
                                       class="form-control form-control-sm @error("niveles.{$n->id}.nota_aprobacion") is-invalid @enderror">
                            </td>
                            <td>
                                <select name="niveles[{{ $n->id }}][aprueba_desde]" class="form-select form-select-sm">
                                    @foreach (\App\Enums\EscalaCalificacion::CONCEPTOS as $sigla => $nombre)
                                        <option value="{{ $sigla }}" @selected(old("niveles.{$n->id}.aprueba_desde", $n->apruebaDesde()) === $sigla)>{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <span class="small text-muted">Los valores sugeridos dependen de cada jurisdicción: ajustalos a la normativa de la institución.</span>
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
        </div>
    </div>
</form>

@endsection
