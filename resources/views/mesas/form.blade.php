@extends('layouts.app')

@section('titulo', $mesa->exists ? 'Editar mesa' : 'Nueva mesa de examen')

@section('contenido')

@php $f = fn ($c, $formato = 'Y-m-d') => old($c, $mesa->{$c} instanceof \Carbon\CarbonInterface ? $mesa->{$c}->format($formato) : $mesa->{$c}); @endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">{{ $mesa->exists ? 'Editar mesa' : 'Nueva mesa de examen' }}</h1>
    <a href="{{ $mesa->exists ? route('mesas.show', $mesa) : route('mesas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ $mesa->exists ? route('mesas.update', $mesa) : route('mesas.store') }}" class="card">
            @csrf
            @if ($mesa->exists) @method('PUT') @endif
            <div class="card-body row g-3">
                <div class="col-12">
                    <label for="espacio_curricular_id" class="form-label">Unidad curricular <span class="text-danger">*</span></label>
                    <select name="espacio_curricular_id" id="espacio_curricular_id" class="form-select @error('espacio_curricular_id') is-invalid @enderror" required>
                        <option value="">Elegí…</option>
                        @foreach ($espacios as $lista)
                            <optgroup label="{{ $lista->first()->plan->nombre }}">
                                @foreach ($lista as $e)
                                    <option value="{{ $e->id }}" @selected((int) $f('espacio_curricular_id') === $e->id)>{{ $e->grado }}° · {{ $e->nombre }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('espacio_curricular_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-7">
                    <label for="llamado" class="form-label">Llamado <span class="text-danger">*</span></label>
                    <input type="text" name="llamado" id="llamado" maxlength="80" value="{{ $f('llamado') }}"
                           class="form-control @error('llamado') is-invalid @enderror" placeholder="Diciembre {{ date('Y') }} - 1° llamado" required>
                    @error('llamado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-5">
                    <label for="fecha" class="form-label">Fecha y hora <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="fecha" id="fecha" value="{{ $f('fecha', 'Y-m-d\TH:i') }}"
                           class="form-control @error('fecha') is-invalid @enderror" required>
                    @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="presidente_id" class="form-label">Presidente/a del tribunal</label>
                    <select name="presidente_id" id="presidente_id" class="form-select">
                        <option value="">—</option>
                        @foreach ($docentes as $d)
                            <option value="{{ $d->id }}" @selected((int) $f('presidente_id') === $d->id)>{{ $d->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="vocales" class="form-label">Vocales</label>
                    <input type="text" name="vocales" id="vocales" maxlength="200" value="{{ $f('vocales') }}" class="form-control" placeholder="Apellidos de los vocales">
                </div>
                <div class="col-md-4">
                    <label for="inscripcion_desde" class="form-label">Inscripción desde <span class="text-danger">*</span></label>
                    <input type="date" name="inscripcion_desde" id="inscripcion_desde" value="{{ $f('inscripcion_desde') }}"
                           class="form-control @error('inscripcion_desde') is-invalid @enderror" required>
                </div>
                <div class="col-md-4">
                    <label for="inscripcion_hasta" class="form-label">Inscripción hasta <span class="text-danger">*</span></label>
                    <input type="date" name="inscripcion_hasta" id="inscripcion_hasta" value="{{ $f('inscripcion_hasta') }}"
                           class="form-control @error('inscripcion_hasta') is-invalid @enderror" required>
                    @error('inscripcion_hasta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="aula" class="form-label">Aula</label>
                    <input type="text" name="aula" id="aula" maxlength="40" value="{{ $f('aula') }}" class="form-control">
                </div>
                <div class="col-12">
                    <input type="hidden" name="admite_libres" value="0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="admite_libres" id="admite_libres" value="1" @checked($f('admite_libres'))>
                        <label class="form-check-label" for="admite_libres">Admite estudiantes libres</label>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection
