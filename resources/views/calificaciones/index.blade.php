@extends('layouts.app')

@section('titulo', 'Calificaciones')

@section('contenido')

@php
    $puedeCorregir = auth()->user()->rol->esGestion();
    $escala = $nivel?->escalaCalificacion();
    $faltan = $periodo ? $estudiantes->count() * $espacios->count() - $notas->count() : 0;
    $volver = request()->fullUrl();
    $params = fn (array $extra = []) => array_filter(array_merge([
        'ciclo' => $ciclo?->id, 'nivel' => $nivel?->id, 'periodo' => $periodo?->id,
        'curso' => $nivel?->nivel->usaCursos() ? $grupo?->id : null,
        'plan'  => $nivel && ! $nivel->nivel->usaCursos() ? $grupo?->id : null,
        'anio'  => $anio,
    ], $extra), fn ($v) => $v !== null);
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Calificaciones</h1>
        <p class="text-muted mb-0">Las cargan los docentes en GestiónAula. Acá se consultan y, si hace falta, se corrigen.</p>
    </div>
    @if ($ciclos->count() > 1)
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="small text-muted">Ciclo</label>
            <select name="ciclo" class="form-select" onchange="this.form.submit()">
                @foreach ($ciclos as $c)
                    <option value="{{ $c->id }}" @selected($ciclo?->id === $c->id)>{{ $c->anio }}</option>
                @endforeach
            </select>
        </form>
    @endif
</div>

@if (! $ciclo)
    <div class="alert alert-info">Primero creá un ciclo lectivo.</div>
@else
    <ul class="nav nav-tabs">
        @foreach ($niveles as $n)
            <li class="nav-item">
                <a class="nav-link {{ $nivel?->id === $n->id ? 'active' : '' }}" href="{{ route('calificaciones.index', ['ciclo' => $ciclo->id, 'nivel' => $n->id]) }}">
                    <i class="bi {{ $n->nivel->icono() }} me-1"></i>{{ $n->nivel->labelCorto() }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card rounded-top-0 mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="ciclo" value="{{ $ciclo->id }}">
                <input type="hidden" name="nivel" value="{{ $nivel->id }}">
                @if ($nivel->nivel->usaCursos())
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Curso</label>
                        <select name="curso" class="form-select" onchange="this.form.submit()">
                            @foreach ($grupos as $g)
                                <option value="{{ $g->id }}" @selected($grupo?->id === $g->id)>{{ $g->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="col-md-5">
                        <label class="form-label small text-muted mb-1">Carrera</label>
                        <select name="plan" class="form-select" onchange="this.form.submit()">
                            @foreach ($grupos as $g)
                                <option value="{{ $g->id }}" @selected($grupo?->id === $g->id)>{{ $g->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($grupo)
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Año</label>
                            <select name="anio" class="form-select" onchange="this.form.submit()">
                                @foreach ($grupo->grados() as $g)
                                    <option value="{{ $g }}" @selected($anio === $g)>{{ $g }}° año</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @endif
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Período</label>
                    <select name="periodo" class="form-select" onchange="this.form.submit()">
                        @foreach ($periodos as $p)
                            <option value="{{ $p->id }}" @selected($periodo?->id === $p->id)>
                                {{ $p->nombre }}{{ $p->cerrado ? ' (cerrado)' : '' }}{{ $p->estaVigente() ? ' · vigente' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if (! $grupo || ! $periodo)
        <div class="alert alert-info">No hay {{ $nivel->nivel->usaCursos() ? 'cursos' : 'carreras' }} o períodos para mostrar.</div>
    @elseif ($espacios->isEmpty())
        <div class="alert alert-warning">
            {{ $nivel->nivel->usaCursos() && ! $grupo->plan_estudio_id ? 'El curso no tiene plan de estudio asignado.' : 'No hay espacios curriculares para mostrar.' }}
        </div>
    @else
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div class="small">
                <span class="badge text-bg-light border">{{ $escala->label() }}</span>
                @if ($escala === \App\Enums\EscalaCalificacion::Numerica)
                    <span class="text-muted">· aprueba con {{ rtrim(rtrim(number_format($nivel->notaAprobacion(), 2, ',', ''), '0'), ',') }}</span>
                @endif
                @if ($periodo->cerrado) · <span class="text-danger"><i class="bi bi-lock"></i> Período cerrado</span> @endif
            </div>
            <div class="small {{ $faltan ? 'text-warning-emphasis' : 'text-success' }}">
                <i class="bi {{ $faltan ? 'bi-hourglass-split' : 'bi-check-circle' }}"></i>
                {{ $faltan ? "Faltan {$faltan} notas" : 'Todas las notas cargadas' }}
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 190px">Estudiante</th>
                            @foreach ($espacios as $e)
                                <th class="text-center" style="min-width: 80px" title="{{ $e->nombre }}">{{ Str::limit($e->nombre, 18) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($estudiantes as $est)
                            <tr>
                                <td>
                                    <a href="{{ route('calificaciones.boletin', $est) }}" class="text-body text-decoration-none" title="Ver boletín">{{ $est->nombre_completo }}</a>
                                </td>
                                @foreach ($espacios as $e)
                                    @php
                                        $c = $notas->get("{$est->id}-{$e->id}");
                                        $aprueba = $c ? $nivel->aprueba($c->valor, $c->valor_numerico) : null;
                                        $url = $puedeCorregir ? route('calificaciones.cargar', ['estudiante' => $est->id, 'espacio' => $e->id, 'periodo' => $periodo->id, 'volver' => $volver]) : null;
                                    @endphp
                                    <td class="text-center {{ $aprueba === false ? 'table-danger' : '' }} {{ ! $c ? 'text-muted' : '' }}"
                                        title="{{ $c?->observacion }}">
                                        @if ($url) <a href="{{ $url }}" class="d-block text-decoration-none {{ $aprueba === false ? 'text-danger-emphasis' : 'text-body' }}"> @endif
                                        @if (! $c)
                                            ·
                                        @elseif ($escala === \App\Enums\EscalaCalificacion::Informe)
                                            <i class="bi bi-file-text" title="{{ Str::limit($c->informe, 200) }}"></i>
                                        @else
                                            <span class="fw-semibold">{{ $c->valor }}</span>
                                        @endif
                                        @if ($c?->esManual()) <i class="bi bi-pencil-fill text-primary" style="font-size: .6rem" title="Corregida en secretaría"></i> @endif
                                        @if ($url) </a> @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ $espacios->count() + 1 }}" class="text-center text-muted py-4">Sin estudiantes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted">
                · sin nota · en rojo, desaprobada · <i class="bi bi-pencil-fill text-primary" style="font-size: .6rem"></i> corregida en secretaría (no la pisa GestiónAula).
                @if ($puedeCorregir) Hacé clic en una celda para cargar o corregir. @endif
            </div>
        </div>
    @endif
@endif

@endsection
