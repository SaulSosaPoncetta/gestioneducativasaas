@extends('layouts.app')

@section('titulo', 'Analítico de ' . $estudiante->nombre_completo)

@push('estilos')
<style>
    @media print {
        body { background: #fff !important; }
        .card { box-shadow: none !important; border: 1px solid #999 !important; }
        @page { margin: 12mm; }
    }
</style>
@endpush

@section('contenido')

@php $num = fn ($v) => $v === null ? '' : rtrim(rtrim(number_format($v, 2, ',', ''), '0'), ','); @endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
    <h1 class="h3 mb-0">Analítico</h1>
    <div class="d-flex gap-2">
        @if ($carreras->count() > 1)
            <form method="GET">
                <select name="plan" class="form-select" onchange="this.form.submit()">
                    @foreach ($carreras as $c)
                        <option value="{{ $c->id }}" @selected($plan?->id === $c->id)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </form>
        @endif
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
        <a href="{{ route('estudiantes.show', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Legajo</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body d-flex flex-wrap gap-3 align-items-center">
        @if ($institucionActual->logo_url)
            <img src="{{ $institucionActual->logo_url }}" alt="Logo" style="height:60px;width:60px;object-fit:contain">
        @endif
        <div class="flex-grow-1">
            <div class="fw-semibold fs-5">{{ $institucionActual->nombre }}</div>
            <div class="small text-muted">
                {{ $plan?->nombre }}
                @if ($plan?->resolucion) · Res. {{ $plan->resolucion }} @endif
                @if ($plan?->titulo) · Título: {{ $plan->titulo }} @endif
            </div>
        </div>
        <div class="text-end">
            <div class="fw-semibold">{{ $estudiante->nombre_completo }}</div>
            <div class="small text-muted">{{ $estudiante->documento_label }} · Legajo N° {{ $estudiante->legajo }}</div>
        </div>
    </div>
</div>

@if (! $plan)
    <div class="alert alert-info">El estudiante no está inscripto en ninguna carrera.</div>
@else
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3"><div class="card"><div class="card-body text-center">
            <div class="fs-4 fw-semibold">{{ $totales['aprobadas'] }} / {{ $totales['total'] }}</div><div class="small text-muted">Unidades aprobadas</div>
        </div></div></div>
        <div class="col-6 col-md-3"><div class="card"><div class="card-body text-center">
            <div class="fs-4 fw-semibold">{{ $totales['avance'] }} %</div><div class="small text-muted">Avance de la carrera</div>
            <div class="progress mt-1" style="height: 5px"><div class="progress-bar" style="width: {{ $totales['avance'] }}%"></div></div>
        </div></div></div>
        <div class="col-6 col-md-3"><div class="card"><div class="card-body text-center">
            <div class="fs-4 fw-semibold">{{ $num($totales['promedio']) ?: '—' }}</div><div class="small text-muted">Promedio sin aplazos</div>
        </div></div></div>
        <div class="col-6 col-md-3"><div class="card"><div class="card-body text-center">
            <div class="fs-4 fw-semibold">{{ $num($totales['promedio_aplazos']) ?: '—' }}</div><div class="small text-muted">Con aplazos ({{ $totales['aplazos'] }})</div>
        </div></div></div>
    </div>

    @foreach ($porAnio as $anio => $lista)
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">{{ $anio }}° año</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Unidad curricular</th>
                            <th class="text-center">Condición</th>
                            <th class="text-center">Nota</th>
                            <th class="text-center">Fecha</th>
                            <th class="d-none d-md-table-cell">Forma</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lista as $e)
                            @php [$texto, $color] = \App\Services\TrayectoriaService::estadoLabel($e->estado); @endphp
                            <tr>
                                <td>{{ $e->espacio->nombre }} @if ($e->aplazos) <span class="small text-danger">({{ $e->aplazos }} aplazo{{ $e->aplazos > 1 ? 's' : '' }})</span> @endif</td>
                                <td class="text-center">
                                    <span class="badge text-bg-{{ $color }} {{ $color === 'light' ? 'border' : '' }}">{{ $texto }}</span>
                                    @if ($e->estado === 'regular' && $e->vence) <div class="small text-muted">hasta {{ $e->vence->format('d/m/Y') }}</div> @endif
                                </td>
                                <td class="text-center fw-semibold">{{ $e->estado === 'aprobada' ? $num($e->nota) : '' }}</td>
                                <td class="text-center small">{{ $e->estado === 'aprobada' ? $e->fecha?->format('d/m/Y') : '' }}</td>
                                <td class="d-none d-md-table-cell small">{{ $e->forma }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <p class="small text-muted d-none d-print-block">Emitido el {{ today()->format('d/m/Y') }}.</p>
    <div class="d-none d-print-flex justify-content-around mt-5 pt-4 small text-center">
        <div style="width: 35%; border-top: 1px solid #000">Secretaría</div>
        <div style="width: 35%; border-top: 1px solid #000">Dirección / Rectorado</div>
    </div>
@endif

@endsection
