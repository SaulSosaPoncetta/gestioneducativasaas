@extends('layouts.app')

@section('titulo', 'Boletín de ' . $estudiante->nombre_completo)

@push('estilos')
<style>
    @media print {
        body { background: #fff !important; }
        .card { box-shadow: none !important; border: 1px solid #999 !important; }
        .boletin-cabecera { display: flex !important; }
        a { color: inherit !important; text-decoration: none !important; }
        @page { margin: 12mm; }
    }
</style>
@endpush

@section('contenido')

@php
    $institucion = $institucionActual;
    $num = fn ($v) => $v === null ? '' : rtrim(rtrim(number_format((float) $v, 1, ',', ''), '0'), ',');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
    <h1 class="h3 mb-0">Boletín</h1>
    <div class="d-flex gap-2">
        @if ($ciclos->count() > 1)
            <form method="GET">
                <select name="ciclo" class="form-select" onchange="this.form.submit()">
                    @foreach ($ciclos as $c)
                        <option value="{{ $c->id }}" @selected($ciclo?->id === $c->id)>{{ $c->anio }}</option>
                    @endforeach
                </select>
            </form>
        @endif
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
        <a href="{{ route('estudiantes.show', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Legajo</a>
    </div>
</div>

{{-- Encabezado --}}
<div class="card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center gap-3 boletin-cabecera">
        @if ($institucion->logo_url)
            <img src="{{ $institucion->logo_url }}" alt="Logo" style="height:64px;width:64px;object-fit:contain">
        @endif
        <div class="flex-grow-1">
            <div class="fw-semibold fs-5">{{ $institucion->nombre }}</div>
            <div class="small text-muted">
                @if ($datos) {{ $datos['nivel']->nombre }} · @endif
                {{ $ciclo?->nombre }}
                @if ($institucion->cue) · CUE {{ $institucion->cue }} @endif
            </div>
        </div>
        <div class="text-end">
            <div class="fw-semibold">{{ $estudiante->nombre_completo }}</div>
            <div class="small text-muted">{{ $estudiante->documento_label }} · Legajo N° {{ $estudiante->legajo }}</div>
            @if ($datos)
                <div class="small">{{ $datos['inscripcion']->destino }}</div>
            @endif
        </div>
    </div>
</div>

@if (! $datos)
    <div class="alert alert-info">El estudiante no tiene inscripción en este ciclo lectivo.</div>
@else
    @php
        $escala = $datos['nivel']->escalaCalificacion();
        $periodos = $datos['periodos'];
        $esInforme = $escala === \App\Enums\EscalaCalificacion::Informe;
    @endphp

    @if ($esInforme)
        {{-- Nivel inicial: informes por período --}}
        @foreach ($periodos as $p)
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">{{ $p->nombre }}</div>
                <div class="card-body">
                    @php $hay = false; @endphp
                    @foreach ($datos['filas'] as $f)
                        @if ($n = $f->notas->get($p->id))
                            @php $hay = true; @endphp
                            <div class="mb-3">
                                <div class="fw-semibold small text-uppercase text-muted">{{ $f->espacio->nombre }}</div>
                                <div style="white-space: pre-line">{{ $n->informe }}</div>
                            </div>
                        @endif
                    @endforeach
                    @unless ($hay) <span class="text-muted small">Sin informes en este período.</span> @endunless
                </div>
            </div>
        @endforeach
    @else
        <div class="card mb-3">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">Espacio curricular</th>
                            @foreach ($periodos as $p)
                                <th class="small">{{ $p->nombre }}</th>
                            @endforeach
                            <th class="small">{{ $escala === \App\Enums\EscalaCalificacion::Numerica ? 'Promedio' : 'Final' }}</th>
                            <th class="small">Condición</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datos['filas'] as $f)
                            @php [$condTexto, $condColor] = \App\Services\CalificacionService::condicionLabel($f->situacion['condicion']); @endphp
                            <tr>
                                <td class="text-start">{{ $f->espacio->nombre }}</td>
                                @foreach ($periodos as $p)
                                    @php $n = $f->notas->get($p->id); $ap = $n ? $datos['nivel']->aprueba($n->valor, $n->valor_numerico) : null; @endphp
                                    <td class="{{ $ap === false ? 'text-danger fw-semibold' : '' }}" title="{{ $n?->observacion }}">
                                        {{ $n ? $escala->mostrar($n->valor) : '' }}
                                    </td>
                                @endforeach
                                <td class="fw-semibold">
                                    {{ $escala === \App\Enums\EscalaCalificacion::Numerica ? $num($f->situacion['final']) : $escala->mostrar($f->situacion['final']) }}
                                </td>
                                <td><span class="badge text-bg-{{ $condColor }}">{{ $condTexto }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @php $obs = $datos['filas']->flatMap(fn ($f) => $f->notas->filter(fn ($n) => filled($n->observacion))->map(fn ($n) => [$f->espacio->nombre, $n])); @endphp
        @if ($obs->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold small">Observaciones</div>
                <ul class="list-group list-group-flush small">
                    @foreach ($obs as [$espacio, $n])
                        <li class="list-group-item"><span class="fw-semibold">{{ $espacio }}</span> ({{ $periodos->firstWhere('id', $n->periodo_evaluacion_id)?->nombre }}): {{ $n->observacion }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    {{-- Asistencia --}}
    @php $a = $datos['asistencia']; @endphp
    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold small">Asistencia</div>
        <div class="card-body small d-flex flex-wrap gap-4">
            <span>Días registrados: <strong>{{ $a['dias'] }}</strong></span>
            <span>Inasistencias: <strong>{{ $num($a['inasistencias']) }}</strong></span>
            <span>Justificadas: <strong>{{ $num($a['justificadas']) }}</strong></span>
            <span>Injustificadas: <strong>{{ $num($a['injustificadas']) }}</strong></span>
            @if ($a['porcentaje'] !== null) <span>Asistencia: <strong>{{ number_format($a['porcentaje'], 1, ',', '') }} %</strong></span> @endif
        </div>
    </div>

    <div class="d-none d-print-flex justify-content-around mt-5 pt-4 small text-center">
        <div style="width: 30%; border-top: 1px solid #000">Firma docente / preceptor/a</div>
        <div style="width: 30%; border-top: 1px solid #000">Firma dirección</div>
        <div style="width: 30%; border-top: 1px solid #000">Firma familia</div>
    </div>
@endif

@endsection
