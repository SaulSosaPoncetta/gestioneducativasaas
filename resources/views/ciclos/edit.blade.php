@extends('layouts.app')

@section('titulo', 'Editar ciclo lectivo')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Editar ciclo lectivo</h1>
        <p class="text-muted mb-0">{{ $ciclo->nombre }}</p>
    </div>
    <a href="{{ route('ciclos.show', $ciclo) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<div class="row">
    <div class="col-lg-6">
        <form method="POST" action="{{ route('ciclos.update', $ciclo) }}" class="card">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('ciclos.partials-campos')
                <p class="small text-muted mt-3 mb-0">
                    <i class="bi bi-info-circle"></i> Cambiar estas fechas no mueve los períodos ya generados: se ajustan desde el ciclo.
                </p>
            </div>
            <div class="card-footer bg-white text-end">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection
