@extends('layouts.app')

@section('titulo', 'Inicio')

@section('contenido')

@if ($modo === 'superadmin')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Instituciones</h1>
            <p class="text-muted mb-0">Elegí una institución para trabajar en ella.</p>
        </div>
        @if (Route::has('instituciones.create'))
            <a href="{{ route('instituciones.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Nueva institución
            </a>
        @endif
    </div>

    <div class="row g-3">
        @forelse ($instituciones as $inst)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h2 class="h5 mb-0">{{ $inst->nombre }}</h2>
                            <span class="badge {{ $inst->activo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $inst->activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-geo-alt"></i> {{ $inst->localidad ?: 'Sin localidad' }}
                            @if ($inst->cue) · CUE {{ $inst->cue }} @endif
                            · {{ ucfirst($inst->tipo_gestion) }}
                        </p>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            @foreach ($inst->niveles->where('activo', true) as $n)
                                <span class="badge rounded-pill text-bg-light border">
                                    <i class="bi {{ $n->nivel->icono() }}"></i> {{ $n->nivel->labelCorto() }}
                                </span>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted"><i class="bi bi-people"></i> {{ $inst->usuarios_count }} {{ $inst->usuarios_count === 1 ? 'usuario' : 'usuarios' }}</span>
                            <form method="POST" action="{{ route('institucion.entrar', $inst) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary">
                                    Entrar <i class="bi bi-arrow-right"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">Todavía no hay instituciones cargadas.</div>
            </div>
        @endforelse
    </div>

@else

    <div class="mb-4">
        <h1 class="h3 mb-0">¡Hola, {{ auth()->user()->nombre }}!</h1>
        <p class="text-muted mb-0">{{ $institucion->nombre }} · {{ auth()->user()->rol->label() }}</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="tarjeta-icono bg-primary-subtle text-primary"><i class="bi bi-layers"></i></span>
                    <div>
                        <div class="text-muted small">Niveles</div>
                        <div class="fs-4 fw-semibold">{{ $niveles->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="tarjeta-icono bg-success-subtle text-success"><i class="bi bi-person-badge"></i></span>
                    <div>
                        <div class="text-muted small">Usuarios activos</div>
                        <div class="fs-4 fw-semibold">{{ $totales['usuarios'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="h5 mb-3">Niveles que ofrece la institución</h2>
    <div class="row g-3">
        @forelse ($niveles as $n)
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="tarjeta-icono bg-light text-primary"><i class="bi {{ $n->nivel->icono() }}"></i></span>
                            <div>
                                <div class="fw-semibold">{{ $n->nivel->label() }}</div>
                                @if ($n->denominacion)
                                    <div class="small text-muted">{{ $n->denominacion }}</div>
                                @endif
                            </div>
                        </div>
                        <ul class="list-unstyled small text-muted mb-0">
                            @if ($n->nivel->usaCarreras())
                                <li><i class="bi bi-dot"></i> Organizado por carreras y planes de estudio</li>
                                <li><i class="bi bi-dot"></i> Mesas de examen final con correlatividades</li>
                            @else
                                <li><i class="bi bi-dot"></i> {{ $n->duracion }} {{ Str::plural($n->nivel->unidadCurso(), $n->duracion) }}, por {{ Str::lower($n->nivel->unidadDivision()) }}</li>
                                <li><i class="bi bi-dot"></i> {{ $n->nivel->evaluaConNotas() ? 'Calificaciones por período' : 'Informes cualitativos' }}</li>
                            @endif
                            @if ($n->cue_anexo)
                                <li><i class="bi bi-dot"></i> CUE anexo {{ $n->cue_anexo }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning mb-0">La institución todavía no tiene niveles configurados.</div>
            </div>
        @endforelse
    </div>

@endif

@endsection
