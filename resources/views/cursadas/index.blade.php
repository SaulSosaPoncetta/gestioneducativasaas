@extends('layouts.app')

@section('titulo', 'Cursadas')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Cursadas</h1>
        <p class="text-muted mb-0">Inscriptos por unidad curricular y comisión. Desde cada comisión se cierra la cursada.</p>
    </div>
    <form method="GET" class="d-flex flex-wrap gap-2">
        <select name="plan" class="form-select" onchange="this.form.submit()">
            @foreach ($planes as $p)
                <option value="{{ $p->id }}" @selected($plan?->id === $p->id)>{{ $p->nombre }}</option>
            @endforeach
        </select>
        <select name="ciclo" class="form-select w-auto" onchange="this.form.submit()">
            @foreach ($ciclos as $c)
                <option value="{{ $c->id }}" @selected($ciclo?->id === $c->id)>{{ $c->anio }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="alert alert-light border small">
    <i class="bi bi-info-circle me-1"></i>
    Para inscribir a un estudiante a cursadas, entrá a su legajo y usá <strong>Cursadas</strong>.
    Criterio sugerido de cierre: asistencia mínima {{ \App\Models\Cursada::ASISTENCIA_MINIMA }} %,
    regular con {{ \App\Models\Cursada::NOTA_REGULARIDAD }}, promoción con {{ \App\Models\Cursada::NOTA_PROMOCION }}.
</div>

@if (! $plan)
    <div class="alert alert-info">No hay carreras cargadas.</div>
@else
    @foreach ($espacios->groupBy('grado') as $anio => $lista)
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">{{ $anio }}° año</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Unidad curricular</th>
                            <th class="d-none d-md-table-cell">Régimen</th>
                            <th>Comisiones · inscriptos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lista as $e)
                            @php $porComision = $resumen->get($e->id, collect())->groupBy('comision'); @endphp
                            <tr>
                                <td>{{ $e->nombre }}</td>
                                <td class="d-none d-md-table-cell small">{{ $e->regimen_label }}</td>
                                <td>
                                    @forelse ($porComision as $comision => $filas)
                                        <a href="{{ route('cursadas.comision', ['espacio' => $e, 'ciclo' => $ciclo->id, 'comision' => $comision]) }}"
                                           class="btn btn-sm btn-outline-primary me-1 mb-1">
                                            {{ $comision }} · {{ $filas->sum('total') }}
                                            @php $cursando = $filas->firstWhere('condicion', 'cursando')?->total ?? 0; @endphp
                                            @if ($cursando === 0) <i class="bi bi-check-circle-fill text-success" title="Cursada cerrada"></i> @endif
                                        </a>
                                    @empty
                                        <span class="small text-muted">Sin inscriptos</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif

@endsection
