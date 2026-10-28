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

@include('calificaciones.partials.boletin')

@endsection
