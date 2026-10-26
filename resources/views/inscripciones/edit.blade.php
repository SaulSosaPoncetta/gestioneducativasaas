@extends('layouts.app')

@section('titulo', 'Inscripción')

@section('contenido')

@php
    $estudiante  = $inscripcion->estudiante;
    $condiciones = $inscripcion->curso_id ? \App\Models\Inscripcion::CONDICIONES_ESCOLAR : \App\Models\Inscripcion::CONDICIONES_SUPERIOR;
    $cerrado     = $inscripcion->ciclo->estaCerrado();
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h1 class="h3 mb-0">{{ $inscripcion->destino }}</h1>
            <span class="badge text-bg-{{ $inscripcion->estado_color }}">{{ $inscripcion->estado_label }}</span>
        </div>
        <p class="text-muted mb-0">{{ $estudiante->nombre_completo }} · {{ $inscripcion->ciclo->nombre }} · {{ $inscripcion->nivel->nombre }}</p>
    </div>
    <a href="{{ route('estudiantes.show', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

@if ($cerrado)
    <div class="alert alert-secondary"><i class="bi bi-lock me-1"></i> El {{ $inscripcion->ciclo->nombre }} está cerrado: la inscripción es de solo lectura.</div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('inscripciones.update', $inscripcion) }}" class="card">
            @csrf @method('PUT')
            <div class="card-header bg-white fw-semibold"><i class="bi bi-journal-check me-1"></i> Datos de la inscripción</div>
            <fieldset class="card-body" @disabled($cerrado || ! $inscripcion->estaActiva())>
                <div class="row g-3">
                    @if ($inscripcion->curso_id)
                        <div class="col-12">
                            <label for="curso_id" class="form-label">Curso</label>
                            <select name="curso_id" id="curso_id" class="form-select @error('curso_id') is-invalid @enderror">
                                @foreach ($cursos as $c)
                                    <option value="{{ $c->id }}" @selected((int) old('curso_id', $inscripcion->curso_id) === $c->id)>
                                        {{ $c->nombre }} · {{ $c->turno_label }} — {{ $c->inscripciones_activas_count }}{{ $c->cupo ? '/' . $c->cupo : '' }} inscriptos
                                    </option>
                                @endforeach
                            </select>
                            @error('curso_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="forzar_cupo" id="forzar_cupo" value="1">
                                <label class="form-check-label small" for="forzar_cupo">Cambiar aunque supere el cupo</label>
                            </div>
                        </div>
                    @else
                        <div class="col-12">
                            <label class="form-label">Carrera</label>
                            <input type="text" class="form-control" value="{{ $inscripcion->plan?->nombre }}" disabled>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <label for="fecha" class="form-label">Fecha de inscripción</label>
                        <input type="date" name="fecha" id="fecha" value="{{ old('fecha', $inscripcion->fecha->format('Y-m-d')) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="condicion" class="form-label">Condición</label>
                        <select name="condicion" id="condicion" class="form-select">
                            @foreach ($condiciones as $v => $t)
                                <option value="{{ $v }}" @selected(old('condicion', $inscripcion->condicion) === $v)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" rows="2" maxlength="1000" class="form-control">{{ old('observaciones', $inscripcion->observaciones) }}</textarea>
                    </div>
                </div>
            </fieldset>
            @if (! $cerrado && $inscripcion->estaActiva())
                <div class="card-footer bg-white text-end">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                </div>
            @endif
        </form>
    </div>

    <div class="col-lg-5">
        @unless ($cerrado)
            @if ($inscripcion->estaActiva())
                <form method="POST" action="{{ route('inscripciones.estado', $inscripcion) }}" class="card mb-3"
                      data-confirmar="¿Confirmás el cambio de estado de la inscripción?">
                    @csrf @method('PATCH')
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-box-arrow-right me-1"></i> Baja, pase o finalización</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="estado" class="form-label">Nuevo estado</label>
                            <select name="estado" id="estado" class="form-select">
                                @foreach (\App\Models\Inscripcion::ESTADOS as $v => $t)
                                    @continue($v === 'activa')
                                    <option value="{{ $v }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_baja" class="form-label">Fecha</label>
                            <input type="date" name="fecha_baja" id="fecha_baja" value="{{ today()->format('Y-m-d') }}"
                                   class="form-control @error('fecha_baja') is-invalid @enderror" required>
                            @error('fecha_baja') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="motivo_baja" class="form-label">Motivo</label>
                            <input type="text" name="motivo_baja" id="motivo_baja" maxlength="200" class="form-control"
                                   placeholder="Ej: pase a la EP N° 5 por mudanza">
                        </div>
                    </div>
                    <div class="card-footer bg-white text-end">
                        <button class="btn btn-outline-danger"><i class="bi bi-check-lg me-1"></i> Aplicar</button>
                    </div>
                </form>
            @else
                <div class="card mb-3">
                    <div class="card-body">
                        <p class="small mb-2">
                            {{ $inscripcion->estado_label }} desde el {{ $inscripcion->fecha_baja?->format('d/m/Y') }}
                            @if ($inscripcion->motivo_baja) — {{ $inscripcion->motivo_baja }} @endif
                        </p>
                        @error('estado') <div class="alert alert-danger py-2 small">{{ $message }}</div> @enderror
                        <form method="POST" action="{{ route('inscripciones.estado', $inscripcion) }}" data-confirmar="¿Reactivar la inscripción?">
                            @csrf @method('PATCH')
                            <input type="hidden" name="estado" value="activa">
                            <button class="btn btn-outline-success"><i class="bi bi-arrow-counterclockwise me-1"></i> Reactivar</button>
                        </form>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('inscripciones.destroy', $inscripcion) }}" class="text-end"
                  data-confirmar="¿Eliminar la inscripción? Usalo solo si se cargó por error.">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-link text-danger"><i class="bi bi-trash me-1"></i> Eliminar (cargada por error)</button>
            </form>
        @endunless
    </div>
</div>

@endsection
