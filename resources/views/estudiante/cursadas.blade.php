@extends('layouts.app')

@section('titulo', 'Inscripción a cursadas')

@section('contenido')

@php $abierta = $ciclo?->inscripcionCursadasAbierta(); @endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Inscripción a cursadas</h1>
        <p class="text-muted mb-0">{{ $plan?->nombre }} · {{ $ciclo?->nombre }}</p>
    </div>
    @if ($carreras->count() > 1)
        <form method="GET">
            <select name="plan" class="form-select" onchange="this.form.submit()">
                @foreach ($carreras as $c)
                    <option value="{{ $c->id }}" @selected($plan?->id === $c->id)>{{ $c->nombre }}</option>
                @endforeach
            </select>
        </form>
    @endif
</div>

@if (! $plan)
    <div class="alert alert-info">No estás inscripto en ninguna carrera.</div>
@else
    @if ($abierta)
        <div class="alert alert-success"><i class="bi bi-calendar-check me-1"></i> La inscripción está abierta hasta el {{ $ciclo->inscripcion_cursadas_hasta->format('d/m/Y') }}.</div>
    @else
        <div class="alert alert-secondary">
            <i class="bi bi-calendar-x me-1"></i> La inscripción a cursadas está cerrada.
            @if ($ciclo?->inscripcion_cursadas_desde) Período: {{ $ciclo->inscripcion_cursadas_desde->format('d/m') }} al {{ $ciclo->inscripcion_cursadas_hasta->format('d/m') }}. @endif
        </div>
    @endif

    <form method="POST" action="{{ route('estudiante.cursadas.inscribir') }}">
        @csrf
        <input type="hidden" name="plan" value="{{ $plan->id }}">
        @foreach ($espacios->groupBy(fn ($f) => $f->espacio->grado) as $anio => $lista)
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">{{ $anio }}° año</div>
                <ul class="list-group list-group-flush">
                    @foreach ($lista as $f)
                        @php
                            $estado = $f->estado->estado;
                            [$texto, $color] = \App\Services\TrayectoriaService::estadoLabel($estado);
                            $disponible = $abierta && in_array($estado, ['pendiente', 'libre'], true) && empty($f->faltan);
                        @endphp
                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <label class="d-flex align-items-start gap-2 mb-0 {{ $disponible ? '' : 'text-muted' }}">
                                <input class="form-check-input mt-1" type="checkbox" name="espacios[]" value="{{ $f->espacio->id }}" @disabled(! $disponible)>
                                <span>
                                    {{ $f->espacio->nombre }} <span class="small text-muted">· {{ $f->espacio->regimen_label }}</span>
                                    @if ($f->faltan && in_array($estado, ['pendiente', 'libre'], true))
                                        <span class="d-block small text-danger"><i class="bi bi-lock"></i> Te falta: {{ implode(', ', $f->faltan) }}</span>
                                    @endif
                                </span>
                            </label>
                            <span class="badge text-bg-{{ $color }} {{ $color === 'light' ? 'border' : '' }}">{{ $texto }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        @if ($abierta)
            <div class="text-end"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Inscribirme</button></div>
        @endif
    </form>
@endif

@endsection
