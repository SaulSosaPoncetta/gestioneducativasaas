@extends('layouts.app')

@section('titulo', 'Emitir constancia')

@section('contenido')

<div class="mb-3">
    <a href="{{ route('constancias.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Constancias</a>
    <h1 class="h3 mb-0">Emitir constancia</h1>
    <p class="text-muted mb-0">Buscá al estudiante por apellido, nombre, DNI o legajo.</p>
</div>

<form method="GET" class="card mb-3" style="max-width: 640px">
    <div class="card-body d-flex gap-2">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Ej.: Romero Sofía o 45123456" autofocus>
        <button class="btn btn-primary"><i class="bi bi-search"></i></button>
    </div>
</form>

@if (request()->filled('q'))
    <div class="list-group shadow-sm" style="max-width: 640px">
        @forelse ($resultados as $e)
            <a href="{{ route('constancias.create', ['estudiante' => $e->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <span>
                    <span class="fw-semibold">{{ $e->nombre_completo }}</span>
                    <span class="small text-muted d-block">{{ $e->documento_label }} · Legajo {{ $e->legajo }}</span>
                </span>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
        @empty
            <div class="list-group-item text-muted">No se encontraron estudiantes.</div>
        @endforelse
    </div>
@endif

@endsection
