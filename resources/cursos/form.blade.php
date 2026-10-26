@extends('layouts.app')

@section('titulo', $curso->exists ? 'Editar curso' : 'Nuevo curso')

@section('contenido')

@php $volver = route('cursos.index', ['ciclo' => $ciclo->id, 'nivel' => $nivel->id]); @endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $curso->exists ? 'Editar ' . $curso->nombre : 'Nuevo curso' }}</h1>
        <p class="text-muted mb-0">{{ $nivel->nombre }} · {{ $ciclo->nombre }}</p>
    </div>
    <a href="{{ $volver }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<div class="row">
    <div class="col-lg-7">
        <form method="POST" action="{{ $curso->exists ? route('cursos.update', $curso) : route('cursos.store') }}" class="card">
            @csrf
            @if ($curso->exists) @method('PUT') @endif
            <input type="hidden" name="ciclo_lectivo_id" value="{{ $ciclo->id }}">
            <input type="hidden" name="institucion_nivel_id" value="{{ $nivel->id }}">

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="grado" class="form-label">{{ $nivel->nivel->unidadCurso() }} <span class="text-danger">*</span></label>
                        <select name="grado" id="grado" class="form-select @error('grado') is-invalid @enderror" required>
                            <option value="">Elegí…</option>
                            @foreach ($nivel->nivel->grados($nivel->duracion) as $g)
                                <option value="{{ $g }}" @selected((int) old('grado', $curso->grado) === $g)>{{ $nivel->nivel->etiquetaGrado($g) }}</option>
                            @endforeach
                        </select>
                        @error('grado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-7">
                        @if ($curso->exists)
                            <label for="division" class="form-label">{{ $nivel->nivel->unidadDivision() }} <span class="text-danger">*</span></label>
                            <input type="text" name="division" id="division" maxlength="20" value="{{ old('division', $curso->division) }}"
                                   class="form-control @error('division') is-invalid @enderror" required>
                            @error('division') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @else
                            <label for="divisiones" class="form-label">{{ Str::plural($nivel->nivel->unidadDivision()) }} <span class="text-danger">*</span></label>
                            <input type="text" name="divisiones" id="divisiones" maxlength="150" value="{{ old('divisiones', 'A') }}"
                                   class="form-control @error('divisiones') is-invalid @enderror" required
                                   placeholder="{{ $nivel->nivel === \App\Enums\NivelEducativo::Inicial ? 'Roja, Verde, Amarilla' : 'A, B, C' }}">
                            <div class="form-text">Podés crear varias a la vez separándolas con coma.</div>
                            @error('divisiones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="col-md-5">
                        <label for="turno" class="form-label">Turno <span class="text-danger">*</span></label>
                        <select name="turno" id="turno" class="form-select @error('turno') is-invalid @enderror">
                            @foreach (\App\Models\Curso::TURNOS as $valor => $texto)
                                <option value="{{ $valor }}" @selected(old('turno', $curso->turno) === $valor)>{{ $texto }}</option>
                            @endforeach
                        </select>
                        @error('turno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-7">
                        <label for="preceptor_id" class="form-label">Preceptor/a</label>
                        <select name="preceptor_id" id="preceptor_id" class="form-select @error('preceptor_id') is-invalid @enderror">
                            <option value="">Sin asignar</option>
                            @foreach ($preceptores as $p)
                                <option value="{{ $p->id }}" @selected((int) old('preceptor_id', $curso->preceptor_id) === $p->id)>{{ $p->nombre_completo }}</option>
                            @endforeach
                        </select>
                        @if ($preceptores->isEmpty())
                            <div class="form-text">No hay usuarios con rol Preceptor/a. Se cargan desde Usuarios.</div>
                        @endif
                        @error('preceptor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-5">
                        <label for="aula" class="form-label">Aula</label>
                        <input type="text" name="aula" id="aula" maxlength="40" value="{{ old('aula', $curso->aula) }}"
                               class="form-control @error('aula') is-invalid @enderror">
                        @error('aula') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="cupo" class="form-label">Cupo</label>
                        <input type="number" name="cupo" id="cupo" min="1" max="999" value="{{ old('cupo', $curso->cupo) }}"
                               class="form-control @error('cupo') is-invalid @enderror">
                        @error('cupo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ $volver }}" class="btn btn-outline-secondary">Cancelar</a>
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection
