@extends('layouts.app')

@section('titulo', 'Editar asignación')

@section('contenido')

@php
    $volver = (string) request('volver');
    $volver = str_starts_with($volver, url('/')) ? $volver : route('docentes.show', $asignacion->docente);
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $asignacion->espacio->nombre }} · {{ $asignacion->grupo }}</h1>
        <p class="text-muted mb-0">
            <a href="{{ route('docentes.show', $asignacion->docente) }}">{{ $asignacion->docente->nombre_completo }}</a>
            · {{ $asignacion->espacio->plan->nombre }} · {{ $asignacion->ciclo->nombre }}
        </p>
    </div>
    <a href="{{ $volver }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<div class="row">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('asignaciones.update', $asignacion) }}" class="card">
            @csrf @method('PUT')
            <input type="hidden" name="volver" value="{{ $volver }}">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label for="situacion" class="form-label">Situación de revista</label>
                    <select name="situacion" id="situacion" class="form-select">
                        @foreach (\App\Models\Asignacion::SITUACIONES as $v => $t)
                            <option value="{{ $v }}" @selected(old('situacion', $asignacion->situacion) === $v)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="horas" class="form-label">Hs cátedra</label>
                    <input type="number" name="horas" id="horas" min="1" max="40" value="{{ old('horas', $asignacion->horas) }}"
                           class="form-control @error('horas') is-invalid @enderror">
                </div>
                @unless ($asignacion->curso_id)
                    <div class="col-md-3">
                        <label for="comision" class="form-label">Comisión</label>
                        <input type="text" name="comision" id="comision" maxlength="30" value="{{ old('comision', $asignacion->comision) }}"
                               class="form-control @error('comision') is-invalid @enderror" required>
                    </div>
                @endunless
                <div class="col-md-6">
                    <label for="fecha_desde" class="form-label">Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" value="{{ old('fecha_desde', $asignacion->fecha_desde->format('Y-m-d')) }}"
                           class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="fecha_hasta" class="form-label">Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ old('fecha_hasta', $asignacion->fecha_hasta?->format('Y-m-d')) }}"
                           class="form-control @error('fecha_hasta') is-invalid @enderror">
                    @error('fecha_hasta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="activa" @selected(old('estado', $asignacion->estado) === 'activa')>Activa</option>
                        <option value="finalizada" @selected(old('estado', $asignacion->estado) === 'finalizada')>Finalizada (cese / fin de suplencia)</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="2" maxlength="1000" class="form-control">{{ old('observaciones', $asignacion->observaciones) }}</textarea>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between">
                <button type="submit" form="formEliminar" class="btn btn-link text-danger px-0"><i class="bi bi-trash me-1"></i> Eliminar</button>
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
            </div>
        </form>

        <form method="POST" action="{{ route('asignaciones.destroy', $asignacion) }}" id="formEliminar"
              data-confirmar="¿Eliminar la asignación? Si el docente cesó, mejor marcala como finalizada para conservar el historial.">
            @csrf @method('DELETE')
            <input type="hidden" name="volver" value="{{ $volver }}">
        </form>
    </div>
</div>

@endsection
