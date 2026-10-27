@extends('layouts.app')

@section('titulo', 'Mis cursos')

@section('contenido')

<div class="mb-3">
    <h1 class="h3 mb-0">Mis cursos</h1>
    <p class="text-muted mb-0">@if ($ciclo) {{ $ciclo->nombre }} @endif</p>
</div>

@if (! $docente)
    <div class="alert alert-warning">
        Tu usuario no está vinculado a un legajo docente. Pedile a la secretaría que lo vincule desde Docentes.
    </div>
@elseif ($grupos->isEmpty())
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-easel display-5"></i>
            <p class="mt-3 mb-0">No tenés espacios asignados en el ciclo lectivo actual.</p>
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach ($grupos as $g)
            @php $a = $g->primera; @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="h5 mb-0">{{ $a->curso?->nombre ?? $a->espacio->plan->nombre }}</div>
                                <div class="small text-muted">
                                    @if ($a->curso)
                                        {{ $a->curso->nivel->nivel->labelCorto() }} · {{ $a->curso->turno_label }}
                                        @if ($a->curso->aula) · Aula {{ $a->curso->aula }} @endif
                                    @else
                                        Comisión {{ $a->comision }}
                                    @endif
                                </div>
                            </div>
                            @if ($g->estudiantes !== null)
                                <span class="badge text-bg-light border" title="Estudiantes"><i class="bi bi-people"></i> {{ $g->estudiantes }}</span>
                            @endif
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($g->asignaciones as $asig)
                                <a href="{{ route('docente.cursos.show', $asig) }}" class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center">
                                    <span>
                                        {{ $asig->espacio->nombre }}
                                        @if (! $asig->curso_id) <span class="small text-muted">· {{ $asig->espacio->grado }}° año</span> @endif
                                    </span>
                                    <span class="d-flex align-items-center gap-1">
                                        @if ($asig->situacion !== 'titular')
                                            <span class="badge text-bg-{{ $asig->situacion_color }}">{{ $asig->situacion_label }}</span>
                                        @endif
                                        <i class="bi bi-chevron-right text-muted"></i>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
