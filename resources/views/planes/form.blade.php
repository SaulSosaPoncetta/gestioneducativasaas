@extends('layouts.app')

@php
    $esCarrera = $nivel->nivel->usaCarreras();
    $sustantivo = $esCarrera ? 'carrera' : 'plan';
@endphp

@section('titulo', $plan->exists ? 'Editar ' . $sustantivo : 'Nuevo ' . $sustantivo)

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $plan->exists ? 'Editar ' . $plan->nombre : ($esCarrera ? 'Nueva carrera' : 'Nuevo plan de estudio') }}</h1>
        <p class="text-muted mb-0">{{ $nivel->nombre }}</p>
    </div>
    <a href="{{ $plan->exists ? route('planes.show', $plan) : route('planes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ $plan->exists ? route('planes.update', $plan) : route('planes.store') }}" class="card">
            @csrf
            @if ($plan->exists) @method('PUT') @endif
            <input type="hidden" name="institucion_nivel_id" value="{{ $nivel->id }}">

            <div class="card-body">
                <div class="row g-3">
                    @unless ($esCarrera)
                        <div class="col-md-6">
                            <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror">
                                @foreach ($nivel->nivel->tiposPlan() as $valor => $texto)
                                    <option value="{{ $valor }}" @selected(old('tipo', $plan->tipo) === $valor)>{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endunless

                    <div class="{{ $esCarrera ? 'col-12' : 'col-md-6' }}">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" maxlength="150" value="{{ old('nombre', $plan->nombre) }}"
                               class="form-control @error('nombre') is-invalid @enderror" required
                               placeholder="{{ match (true) {
                                   $esCarrera => 'Profesorado de Educación Primaria',
                                   $nivel->nivel === \App\Enums\NivelEducativo::SecundarioTecnico => 'Técnico en Electromecánica',
                                   $nivel->nivel === \App\Enums\NivelEducativo::Secundario => 'Bachiller en Ciencias Sociales',
                                   default => 'Diseño curricular ' . $nivel->nivel->labelCorto(),
                               } }}">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @if ($esCarrera)
                        <div class="col-md-4">
                            <label for="duracion" class="form-label">Duración (años) <span class="text-danger">*</span></label>
                            <input type="number" name="duracion" id="duracion" min="1" max="8"
                                   value="{{ old('duracion', $plan->grado_hasta - $plan->grado_desde + 1) }}"
                                   class="form-control @error('duracion') is-invalid @enderror" required>
                            @error('duracion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="modalidad" class="form-label">Modalidad</label>
                            <select name="modalidad" id="modalidad" class="form-select @error('modalidad') is-invalid @enderror">
                                @foreach (\App\Models\PlanEstudio::MODALIDADES as $valor => $texto)
                                    <option value="{{ $valor }}" @selected(old('modalidad', $plan->modalidad) === $valor)>{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('modalidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @else
                        @php $grados = $nivel->nivel->grados($nivel->duracion); @endphp
                        <div class="col-6 col-md-4">
                            <label for="grado_desde" class="form-label">Desde <span class="text-danger">*</span></label>
                            <select name="grado_desde" id="grado_desde" class="form-select @error('grado_desde') is-invalid @enderror">
                                @foreach ($grados as $g)
                                    <option value="{{ $g }}" @selected((int) old('grado_desde', $plan->grado_desde) === $g)>{{ $nivel->nivel->etiquetaGrado($g) }}</option>
                                @endforeach
                            </select>
                            @error('grado_desde') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="grado_hasta" class="form-label">Hasta <span class="text-danger">*</span></label>
                            <select name="grado_hasta" id="grado_hasta" class="form-select @error('grado_hasta') is-invalid @enderror">
                                @foreach ($grados as $g)
                                    <option value="{{ $g }}" @selected((int) old('grado_hasta', $plan->grado_hasta) === $g)>{{ $nivel->nivel->etiquetaGrado($g) }}</option>
                                @endforeach
                            </select>
                            @error('grado_hasta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div class="col-md-4">
                        <label for="resolucion" class="form-label">Resolución</label>
                        <input type="text" name="resolucion" id="resolucion" maxlength="100" value="{{ old('resolucion', $plan->resolucion) }}"
                               class="form-control @error('resolucion') is-invalid @enderror" placeholder="Ej: 1234/18">
                        @error('resolucion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="titulo" class="form-label">Título que otorga</label>
                        <input type="text" name="titulo" id="titulo" maxlength="200" value="{{ old('titulo', $plan->titulo) }}"
                               class="form-control @error('titulo') is-invalid @enderror"
                               placeholder="{{ $esCarrera ? 'Profesor/a de Educación Primaria' : 'Bachiller en…' }}">
                        @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <input type="hidden" name="activo" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="activo" id="activo" value="1"
                                   @checked(old('activo', $plan->activo))>
                            <label class="form-check-label" for="activo">Vigente (se pueden inscribir estudiantes)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ $plan->exists ? route('planes.show', $plan) : route('planes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection
