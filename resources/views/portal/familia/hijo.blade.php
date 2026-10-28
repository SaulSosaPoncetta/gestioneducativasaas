@extends('layouts.app')

@section('titulo', $estudiante->nombre . ' ' . $estudiante->apellido)

@push('estilos')
<style>@media print { body { background: #fff !important; } .card { box-shadow: none !important; border: 1px solid #999 !important; } }</style>
@endpush

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
    <h1 class="h3 mb-0">{{ $estudiante->nombre }} {{ $estudiante->apellido }}</h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
        <a href="{{ route('tutor.hijos') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Mis hijos</a>
    </div>
</div>

@if ($plan)
    @php extract($analitico); @endphp
    @include('analitico.partials.contenido')
@else
    @include('calificaciones.partials.boletin')
@endif

{{-- Detalle de inasistencias --}}
@php $faltas = $asistencia['registros']->where('estado', '!=', 'presente'); @endphp
<div class="card mt-3 no-print">
    <div class="card-header bg-white fw-semibold">Inasistencias, llegadas tarde y retiros</div>
    <ul class="list-group list-group-flush small">
        @forelse ($faltas as $f)
            <li class="list-group-item d-flex justify-content-between">
                <span>{{ ucfirst($f->fecha->translatedFormat('l j \d\e F')) }} · <span class="text-{{ \App\Models\Asistencia::color($f->estado) }}">{{ $f->estado_label }}</span></span>
                <span class="text-muted">{{ $f->justificada ? 'Justificada' . ($f->motivo ? ': ' . $f->motivo : '') : 'Sin justificar' }}</span>
            </li>
        @empty
            <li class="list-group-item text-muted">Sin inasistencias registradas.</li>
        @endforelse
    </ul>
    <div class="card-footer bg-white small text-muted">Para justificar una inasistencia, presentá la constancia en preceptoría.</div>
</div>

@endsection
