@extends('layouts.app')

@section('titulo', 'Ciclos lectivos')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Ciclos lectivos</h1>
        <p class="text-muted mb-0">Años lectivos con sus períodos de evaluación por nivel.</p>
    </div>
    <a href="{{ route('ciclos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nuevo ciclo lectivo
    </a>
</div>

<div class="row g-3">
    @forelse ($ciclos as $c)
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('ciclos.show', $c) }}" class="card h-100 text-decoration-none text-body">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="display-6 fw-semibold lh-1">{{ $c->anio }}</div>
                            <div class="text-muted small mt-1">{{ $c->nombre }}</div>
                        </div>
                        <span class="badge text-bg-{{ $c->estado_color }}">{{ $c->estado_label }}</span>
                    </div>
                    <div class="small text-muted">
                        <i class="bi bi-calendar-range"></i>
                        {{ $c->fecha_inicio->format('d/m/Y') }} al {{ $c->fecha_fin->format('d/m/Y') }}
                    </div>
                    <div class="small text-muted">
                        <i class="bi bi-grid-3x3-gap"></i>
                        {{ $c->cursos_count }} {{ $c->cursos_count === 1 ? 'curso' : 'cursos' }}
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar3 display-5 text-primary"></i>
                    <p class="mt-3 mb-3">Todavía no hay ciclos lectivos. Creá el primero para cargar cursos, estudiantes y calificaciones.</p>
                    <a href="{{ route('ciclos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Crear ciclo lectivo</a>
                </div>
            </div>
        </div>
    @endforelse
</div>

@endsection
