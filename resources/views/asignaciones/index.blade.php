@extends('layouts.app')

@section('titulo', 'Asignaciones docentes')

@section('contenido')

@php
    $porcentaje = $totales['espacios'] ? round($totales['cubiertos'] * 100 / $totales['espacios']) : 0;
    $volver = request()->fullUrl();
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Asignaciones docentes</h1>
        <p class="text-muted mb-0">Quién dicta cada espacio curricular @if ($ciclo) en el {{ $ciclo->nombre }} @endif</p>
    </div>
    @if ($ciclos->isNotEmpty())
        <form method="GET" class="d-flex align-items-center gap-2">
            @if ($nivel) <input type="hidden" name="nivel" value="{{ $nivel->id }}"> @endif
            <label for="ciclo" class="small text-muted">Ciclo</label>
            <select name="ciclo" id="ciclo" class="form-select" onchange="this.form.submit()">
                @foreach ($ciclos as $c)
                    <option value="{{ $c->id }}" @selected($ciclo?->id === $c->id)>{{ $c->anio }} · {{ $c->estado_label }}</option>
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
                <a class="nav-link {{ $nivel?->id === $n->id ? 'active' : '' }}" href="{{ route('asignaciones.index', ['ciclo' => $ciclo->id, 'nivel' => $n->id]) }}">
                    <i class="bi {{ $n->nivel->icono() }} me-1"></i>{{ $n->nivel->labelCorto() }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card rounded-top-0 mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <div class="flex-grow-1" style="min-width: 220px">
                <div class="d-flex justify-content-between small mb-1">
                    <span>Espacios con docente</span>
                    <span class="fw-semibold">{{ $totales['cubiertos'] }} de {{ $totales['espacios'] }} ({{ $porcentaje }}%)</span>
                </div>
                <div class="progress" style="height: 8px">
                    <div class="progress-bar {{ $porcentaje === 100 ? 'bg-success' : '' }}" style="width: {{ $porcentaje }}%"></div>
                </div>
            </div>

            @if ($cursosFiltro->isNotEmpty())
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="ciclo" value="{{ $ciclo->id }}">
                    <input type="hidden" name="nivel" value="{{ $nivel->id }}">
                    <select name="curso" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Todos los cursos</option>
                        @foreach ($cursosFiltro as $c)
                            <option value="{{ $c->id }}" @selected((int) request('curso') === $c->id)>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </form>
            @endif

            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="soloVacantes">
                <label class="form-check-label small" for="soloVacantes">Solo sin docente</label>
            </div>
        </div>
    </div>

    @if ($grupos->isEmpty())
        <div class="alert alert-info">
            {{ $nivel?->nivel->usaCursos() ? 'Este nivel no tiene cursos en el ciclo.' : 'Este nivel no tiene carreras vigentes.' }}
        </div>
    @endif

    <div class="vstack gap-3">
        @if ($nivel?->nivel->usaCursos())
            {{-- ===== Niveles escolares: un bloque por curso ===== --}}
            @foreach ($grupos as $curso)
                @php $espacios = $curso->plan?->espacios->where('grado', $curso->grado) ?? collect(); @endphp
                <div class="card grupo">
                    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <span class="fw-semibold">{{ $curso->nombre }}</span>
                            <span class="small text-muted">· {{ $curso->turno_label }} · {{ $curso->plan?->nombre ?? 'Sin plan' }}</span>
                        </div>
                        @if ($curso->plan && ! $ciclo->estaCerrado())
                            <a href="{{ route('asignaciones.create', ['ciclo' => $ciclo->id, 'curso' => $curso->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-person-plus"></i> Asignar docente
                            </a>
                        @endif
                    </div>
                    @if (! $curso->plan)
                        <div class="card-body small text-danger">
                            El curso no tiene plan de estudio. <a href="{{ route('cursos.edit', $curso) }}">Asignale uno</a> para ver sus espacios.
                        </div>
                    @elseif ($espacios->isEmpty())
                        <div class="card-body small text-muted">El plan no tiene espacios cargados para este año.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($espacios as $e)
                                @php $asig = $porClave->get("{$curso->id}-{$e->id}", collect()); @endphp
                                <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 fila {{ $asig->isEmpty() ? 'vacante' : '' }}">
                                    <span>
                                        {{ $e->nombre }}
                                        @if ($e->horas_semanales) <span class="small text-muted">· {{ $e->horas_semanales }} hs</span> @endif
                                    </span>
                                    <span class="d-flex flex-wrap align-items-center gap-1">
                                        @forelse ($asig as $a)
                                            <a href="{{ route('asignaciones.edit', ['asignacion' => $a, 'volver' => $volver]) }}"
                                               class="badge text-bg-{{ $a->situacion_color }} text-decoration-none" title="{{ $a->situacion_label }}">
                                                {{ $a->docente->nombre_completo }}{{ $a->situacion !== 'titular' ? ' (' . $a->situacion_label . ')' : '' }}
                                            </a>
                                        @empty
                                            <span class="badge text-bg-danger-subtle text-danger-emphasis border border-danger-subtle">Sin docente</span>
                                        @endforelse
                                        @unless ($ciclo->estaCerrado())
                                            <a href="{{ route('asignaciones.create', ['ciclo' => $ciclo->id, 'curso' => $curso->id, 'espacio' => $e->id]) }}"
                                               class="btn btn-sm btn-link py-0" title="Asignar"><i class="bi bi-plus-circle"></i></a>
                                        @endunless
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        @else
            {{-- ===== Nivel superior: un bloque por carrera ===== --}}
            @foreach ($grupos as $plan)
                <div class="card grupo">
                    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span class="fw-semibold">{{ $plan->nombre }}</span>
                        @unless ($ciclo->estaCerrado())
                            <a href="{{ route('asignaciones.create', ['ciclo' => $ciclo->id, 'plan' => $plan->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-person-plus"></i> Asignar docente
                            </a>
                        @endunless
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach ($plan->espacios as $e)
                            @php $asig = $porClave->get("p-{$e->id}", collect()); @endphp
                            <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 fila {{ $asig->isEmpty() ? 'vacante' : '' }}">
                                <span>
                                    <span class="badge text-bg-light border me-1">{{ $e->grado }}°</span>
                                    {{ $e->nombre }}
                                    <span class="small text-muted">· {{ $e->regimen_label }}</span>
                                </span>
                                <span class="d-flex flex-wrap align-items-center gap-1">
                                    @forelse ($asig as $a)
                                        <a href="{{ route('asignaciones.edit', ['asignacion' => $a, 'volver' => $volver]) }}"
                                           class="badge text-bg-{{ $a->situacion_color }} text-decoration-none">
                                            {{ $a->docente->nombre_completo }} · Com. {{ $a->comision }}
                                        </a>
                                    @empty
                                        <span class="badge text-bg-danger-subtle text-danger-emphasis border border-danger-subtle">Sin docente</span>
                                    @endforelse
                                    @unless ($ciclo->estaCerrado())
                                        <a href="{{ route('asignaciones.create', ['ciclo' => $ciclo->id, 'plan' => $plan->id, 'espacio' => $e->id]) }}"
                                           class="btn btn-sm btn-link py-0" title="Asignar"><i class="bi bi-plus-circle"></i></a>
                                    @endunless
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @endif
    </div>
@endif

@push('scripts')
<script>
    // "Solo sin docente": oculta los espacios cubiertos y los cursos completos.
    document.addEventListener('DOMContentLoaded', () => {
        const sw = document.getElementById('soloVacantes');
        if (!sw) return;
        const aplicar = () => {
            document.querySelectorAll('.fila').forEach((f) => f.classList.toggle('d-none', sw.checked && !f.classList.contains('vacante')));
            document.querySelectorAll('.grupo').forEach((g) => {
                const filas = g.querySelectorAll('.fila');
                g.classList.toggle('d-none', sw.checked && filas.length > 0 && !g.querySelector('.fila.vacante'));
            });
        };
        sw.addEventListener('change', aplicar);
    });
</script>
@endpush

@endsection
