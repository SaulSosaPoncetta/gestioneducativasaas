@extends('layouts.app')

@section('titulo', 'Inscribir')

@section('contenido')

@php
    $escolares  = $niveles->filter(fn ($n) => $n->nivel->usaCursos());
    $superiores = $niveles->filter(fn ($n) => $n->nivel->usaCarreras());
    $tipo = old('tipo', $escolares->isNotEmpty() ? 'curso' : 'carrera');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Inscribir a {{ $estudiante->nombre_completo }}</h1>
        <p class="text-muted mb-0">Legajo N° {{ $estudiante->legajo }} · {{ $estudiante->documento_label }}</p>
    </div>
    <a href="{{ route('estudiantes.show', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<div class="row">
    <div class="col-xl-8">
        {{-- Ciclo (recarga la página para traer sus cursos) --}}
        <form method="GET" class="card mb-3">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <label for="ciclo" class="fw-semibold me-2">Ciclo lectivo</label>
                <select name="ciclo" id="ciclo" class="form-select w-auto" onchange="this.form.submit()">
                    @foreach ($ciclos as $c)
                        <option value="{{ $c->id }}" @selected($ciclo->id === $c->id)>{{ $c->nombre }} · {{ $c->estado_label }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <form method="POST" action="{{ route('estudiantes.inscripciones.store', $estudiante) }}" class="card">
            @csrf
            <input type="hidden" name="ciclo_lectivo_id" value="{{ $ciclo->id }}">
            <input type="hidden" name="tipo" id="tipo" value="{{ $tipo }}">

            @if ($escolares->isNotEmpty() && $superiores->isNotEmpty())
                <div class="card-header bg-white">
                    <ul class="nav nav-pills card-header-pills">
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ $tipo === 'curso' ? 'active' : '' }}" data-bs-toggle="pill"
                                    data-bs-target="#panel_curso" data-tipo="curso">Curso (inicial, primaria, secundaria)</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ $tipo === 'carrera' ? 'active' : '' }}" data-bs-toggle="pill"
                                    data-bs-target="#panel_carrera" data-tipo="carrera">Carrera (nivel superior)</button>
                        </li>
                    </ul>
                </div>
            @endif

            <div class="card-body">
                <div class="tab-content">
                    {{-- Curso --}}
                    <div class="tab-pane fade {{ $tipo === 'curso' ? 'show active' : '' }}" id="panel_curso">
                        @if ($escolares->isNotEmpty())
                            <label for="curso_id" class="form-label">Curso <span class="text-danger">*</span></label>
                            <select name="curso_id" id="curso_id" class="form-select @error('curso_id') is-invalid @enderror">
                                <option value="">Elegí el curso…</option>
                                @foreach ($escolares as $n)
                                    @if ($cursos->has($n->id))
                                        <optgroup label="{{ $n->nombre }}">
                                            @foreach ($cursos[$n->id] as $c)
                                                @php $lleno = $c->cupo && $c->inscripciones_activas_count >= $c->cupo; @endphp
                                                <option value="{{ $c->id }}" @selected((int) old('curso_id') === $c->id)>
                                                    {{ $c->nombre }} · {{ $c->turno_label }}
                                                    — {{ $c->inscripciones_activas_count }}{{ $c->cupo ? '/' . $c->cupo : '' }} inscriptos{{ $lleno ? ' (completo)' : '' }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                            @error('curso_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if ($cursos->isEmpty())
                                <div class="form-text text-danger">El {{ $ciclo->nombre }} no tiene cursos. Se arman desde Cursos y divisiones.</div>
                            @endif

                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="forzar_cupo" id="forzar_cupo" value="1" @checked(old('forzar_cupo'))>
                                <label class="form-check-label small" for="forzar_cupo">Inscribir aunque supere el cupo</label>
                            </div>
                        @endif
                    </div>

                    {{-- Carrera --}}
                    <div class="tab-pane fade {{ $tipo === 'carrera' ? 'show active' : '' }}" id="panel_carrera">
                        @if ($superiores->isNotEmpty())
                            <label for="plan_estudio_id" class="form-label">Carrera <span class="text-danger">*</span></label>
                            <select name="plan_estudio_id" id="plan_estudio_id" class="form-select @error('plan_estudio_id') is-invalid @enderror">
                                <option value="">Elegí la carrera…</option>
                                @foreach ($carreras->groupBy('institucion_nivel_id') as $lista)
                                    <optgroup label="{{ $lista->first()->nivel->nombre }}">
                                        @foreach ($lista as $p)
                                            <option value="{{ $p->id }}" @selected((int) old('plan_estudio_id') === $p->id)>{{ $p->nombre }} ({{ $p->alcance }})</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('plan_estudio_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if ($carreras->isEmpty())
                                <div class="form-text text-danger">No hay carreras vigentes. Se cargan desde Planes de estudio.</div>
                            @endif
                        @endif
                    </div>
                </div>

                @error('ciclo_lectivo_id') <div class="alert alert-danger py-2 small mt-3 mb-0">{{ $message }}</div> @enderror

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label for="fecha" class="form-label">Fecha de inscripción <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" id="fecha" value="{{ old('fecha', today()->format('Y-m-d')) }}"
                               class="form-control @error('fecha') is-invalid @enderror" required>
                        @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="condicion" class="form-label">Condición</label>
                        <select name="condicion" id="condicion" class="form-select">
                            @foreach (\App\Models\Inscripcion::CONDICIONES_ESCOLAR as $v => $t)
                                <option value="{{ $v }}" data-para="curso" @selected(old('condicion') === $v)>{{ $t }}</option>
                            @endforeach
                            @foreach (\App\Models\Inscripcion::CONDICIONES_SUPERIOR as $v => $t)
                                <option value="{{ $v }}" data-para="carrera" @selected(old('condicion') === $v)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" rows="2" maxlength="1000" class="form-control"
                                  placeholder="Ej: viene con pase de otra escuela">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('estudiantes.show', $estudiante) }}" class="btn btn-outline-secondary">Cancelar</a>
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Inscribir</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Cambia el tipo (curso / carrera) y las condiciones que corresponden.
    document.addEventListener('DOMContentLoaded', () => {
        const tipo = document.getElementById('tipo');
        const condicion = document.getElementById('condicion');

        const aplicar = (t) => {
            tipo.value = t;
            let primera = null;
            [...condicion.options].forEach((o) => {
                const ok = o.dataset.para === t;
                o.hidden = !ok; o.disabled = !ok;
                if (ok && !primera) primera = o;
            });
            if (condicion.selectedOptions[0]?.disabled) condicion.value = primera.value;
        };

        document.querySelectorAll('[data-tipo]').forEach((b) => b.addEventListener('shown.bs.tab', () => aplicar(b.dataset.tipo)));
        aplicar(tipo.value);
    });
</script>
@endpush

@endsection
