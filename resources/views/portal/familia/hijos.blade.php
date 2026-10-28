@extends('layouts.app')

@section('titulo', 'Mis hijos')

@section('contenido')

@php $num = fn ($v) => rtrim(rtrim(number_format((float) $v, 1, ',', ''), '0'), ','); @endphp

<div class="mb-3">
    <h1 class="h3 mb-0">¡Hola, {{ $tutor->nombre }}!</h1>
    <p class="text-muted mb-0">{{ $ciclo?->nombre }}</p>
</div>

@if ($hijos->isEmpty())
    <div class="alert alert-info">No tenés estudiantes a cargo vinculados. Consultá en secretaría.</div>
@endif

<div class="row g-3">
    @foreach ($hijos as $h)
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="avatar" style="width:48px;height:48px;font-size:1.05rem">{{ mb_substr($h->e->nombre, 0, 1) }}{{ mb_substr($h->e->apellido, 0, 1) }}</span>
                            <div>
                                <div class="fw-semibold fs-5">{{ $h->e->nombre }} {{ $h->e->apellido }}</div>
                                <div class="small text-muted">{{ $h->inscripcion?->destino ?? 'Sin inscripción en el ciclo' }}
                                    @if ($h->inscripcion?->curso) · {{ $h->inscripcion->curso->turno_label }} @endif
                                </div>
                            </div>
                        </div>
                        @if ($h->resumen['porcentaje'] !== null)
                            <span class="badge fs-6 {{ $h->resumen['porcentaje'] < 85 ? 'text-bg-danger' : 'text-bg-success' }}" title="Asistencia del ciclo">
                                {{ number_format($h->resumen['porcentaje'], 0) }} %
                            </span>
                        @endif
                    </div>

                    {{-- Avisos --}}
                    <div class="vstack gap-1 my-3">
                        @forelse ($h->recientes as $a)
                            <div class="small alert alert-{{ $a->estado === 'ausente' ? 'danger' : 'warning' }} py-1 px-2 mb-0">
                                <i class="bi bi-calendar-x me-1"></i>
                                {{ ucfirst($a->fecha->translatedFormat('l j/m')) }}: {{ $a->estado_label }}
                                {{ $a->justificada ? '(justificada)' : '' }}
                            </div>
                        @empty
                            <div class="small text-success"><i class="bi bi-check-circle me-1"></i> Sin inasistencias en los últimos 7 días.</div>
                        @endforelse
                        @if ($h->desaprobadas->isNotEmpty())
                            <div class="small alert alert-warning py-1 px-2 mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                {{ $h->desaprobadas->count() }} {{ $h->desaprobadas->count() === 1 ? 'nota desaprobada' : 'notas desaprobadas' }}:
                                {{ $h->desaprobadas->map(fn ($c) => $c->espacio->nombre . ' (' . $c->valor . ')')->unique()->take(4)->join(', ') }}
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center small text-muted">
                        <span>Inasistencias: {{ $num($h->resumen['inasistencias']) }} ({{ $num($h->resumen['justificadas']) }} justificadas)</span>
                        <a href="{{ route('tutor.hijo', $h->e) }}" class="btn btn-sm btn-primary">Ver boletín y asistencia</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection
