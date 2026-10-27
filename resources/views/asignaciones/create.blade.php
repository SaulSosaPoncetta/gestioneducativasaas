@extends('layouts.app')

@section('titulo', 'Asignar docente')

@section('contenido')

@php $grupoElegido = $curso || $plan; @endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Asignar docente</h1>
        <p class="text-muted mb-0">{{ $ciclo->nombre }}</p>
    </div>
    <a href="{{ route('asignaciones.index', ['ciclo' => $ciclo->id, 'nivel' => $curso?->institucion_nivel_id ?? $plan?->institucion_nivel_id]) }}"
       class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

{{-- 1. Elegir curso o carrera --}}
<form method="GET" class="card mb-3">
    <input type="hidden" name="ciclo" value="{{ $ciclo->id }}">
    @if ($docenteId) <input type="hidden" name="docente" value="{{ $docenteId }}"> @endif
    <div class="card-body row g-2 align-items-end">
        @if ($cursos->isNotEmpty())
            <div class="col-md-5">
                <label class="form-label">Curso</label>
                <select name="curso" class="form-select" onchange="this.form.plan && (this.form.plan.value=''); this.form.submit()">
                    <option value="">Elegí…</option>
                    @foreach ($cursos->groupBy('institucion_nivel_id') as $lista)
                        <optgroup label="{{ $lista->first()->nivel->nombre }}">
                            @foreach ($lista as $c)
                                <option value="{{ $c->id }}" @selected($curso?->id === $c->id)>{{ $c->nombre }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
        @endif
        @if ($carreras->isNotEmpty())
            <div class="col-md-5">
                <label class="form-label">Carrera</label>
                <select name="plan" class="form-select" onchange="this.form.curso && (this.form.curso.value=''); this.form.submit()">
                    <option value="">Elegí…</option>
                    @foreach ($carreras as $p)
                        <option value="{{ $p->id }}" @selected($plan?->id === $p->id)>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
</form>

@if ($grupoElegido)
    {{-- 2. Docente, situación y espacios --}}
    <form method="POST" action="{{ route('asignaciones.store') }}">
        @csrf
        <input type="hidden" name="ciclo_lectivo_id" value="{{ $ciclo->id }}">
        @if ($curso) <input type="hidden" name="curso_id" value="{{ $curso->id }}"> @endif
        @if ($plan) <input type="hidden" name="plan_estudio_id" value="{{ $plan->id }}"> @endif

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-person-badge me-1"></i> Docente</div>
                    <div class="card-body vstack gap-3">
                        <div>
                            <label for="docente_id" class="form-label">Docente <span class="text-danger">*</span></label>
                            <select name="docente_id" id="docente_id" class="form-select @error('docente_id') is-invalid @enderror" required>
                                <option value="">Elegí…</option>
                                @foreach ($docentes as $d)
                                    <option value="{{ $d->id }}" @selected((int) old('docente_id', $docenteId) === $d->id)>
                                        {{ $d->nombre_completo }}{{ $d->estado === 'licencia' ? ' (de licencia)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('docente_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if ($docentes->isEmpty())
                                <div class="form-text">No hay docentes. <a href="{{ route('docentes.create') }}">Cargá uno</a>.</div>
                            @endif
                        </div>
                        <div>
                            <label for="situacion" class="form-label">Situación de revista</label>
                            <select name="situacion" id="situacion" class="form-select">
                                @foreach (\App\Models\Asignacion::SITUACIONES as $v => $t)
                                    <option value="{{ $v }}" @selected(old('situacion', 'titular') === $v)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($plan)
                            <div>
                                <label for="comision" class="form-label">Comisión</label>
                                <input type="text" name="comision" id="comision" maxlength="30" value="{{ old('comision', 'Única') }}" class="form-control">
                            </div>
                        @endif
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="fecha_desde" class="form-label">Desde</label>
                                <input type="date" name="fecha_desde" id="fecha_desde" class="form-control @error('fecha_desde') is-invalid @enderror"
                                       value="{{ old('fecha_desde', max($ciclo->fecha_inicio, today())->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-6">
                                <label for="fecha_hasta" class="form-label">Hasta</label>
                                <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ old('fecha_hasta') }}"
                                       class="form-control @error('fecha_hasta') is-invalid @enderror">
                                @error('fecha_hasta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div>
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <input type="text" name="observaciones" id="observaciones" maxlength="1000" value="{{ old('observaciones') }}"
                                   class="form-control" placeholder="Ej: suplencia por licencia de la titular">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span class="fw-semibold"><i class="bi bi-journal-text me-1"></i> {{ $curso?->nombre ?? $plan->nombre }}</span>
                        @if ($espacios->count() > 1)
                            <button type="button" class="btn btn-sm btn-link" id="marcarTodos">Marcar todos</button>
                        @endif
                    </div>
                    <div class="card-body">
                        @error('espacios') <div class="alert alert-danger py-2 small">{{ $message }}</div> @enderror
                        @error('curso_id') <div class="alert alert-danger py-2 small">{{ $message }}</div> @enderror
                        @if ($espacios->isEmpty())
                            <p class="text-muted small mb-0">
                                {{ $curso && ! $curso->plan ? 'El curso no tiene plan de estudio asignado.' : 'No hay espacios curriculares para elegir.' }}
                            </p>
                        @else
                            <div class="vstack gap-1">
                                @foreach ($espacios as $e)
                                    @php $actuales = $ocupados->get($e->id, collect()); @endphp
                                    <label class="form-check border rounded px-2 py-2 ps-4 mb-0">
                                        <input class="form-check-input espacio" type="checkbox" name="espacios[]" value="{{ $e->id }}"
                                               @checked(in_array($e->id, array_map('intval', old('espacios', $espacioId ? [$espacioId] : []))))>
                                        <span class="form-check-label d-flex flex-wrap justify-content-between gap-2">
                                            <span>
                                                @if ($plan) <span class="badge text-bg-light border me-1">{{ $e->grado }}°</span> @endif
                                                {{ $e->nombre }}
                                                @if ($e->horas_semanales) <span class="text-muted small">· {{ $e->horas_semanales }} hs</span> @endif
                                            </span>
                                            <span class="small {{ $actuales->isEmpty() ? 'text-danger' : 'text-muted' }}">
                                                {{ $actuales->isEmpty() ? 'Sin docente' : $actuales->map(fn ($a) => $a->docente->apellido . ($plan ? ' (' . $a->comision . ')' : ''))->join(', ') }}
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Asignar</button>
        </div>
    </form>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('marcarTodos')?.addEventListener('click', () => {
            const cajas = [...document.querySelectorAll('.espacio')];
            const todas = cajas.every((c) => c.checked);
            cajas.forEach((c) => (c.checked = !todas));
        });
    });
</script>
@endpush

@endsection
