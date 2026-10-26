@extends('layouts.app')

@section('titulo', $estudiante->exists ? 'Editar estudiante' : 'Nuevo estudiante')

@section('contenido')

@php
    $valor = fn ($c) => old($c, $estudiante->{$c} instanceof \Carbon\CarbonInterface ? $estudiante->{$c}->format('Y-m-d') : $estudiante->{$c});
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $estudiante->exists ? 'Editar ' . $estudiante->nombre_completo : 'Nuevo estudiante' }}</h1>
        @if ($estudiante->exists) <p class="text-muted mb-0">Legajo N° {{ $estudiante->legajo }}</p> @endif
    </div>
    <a href="{{ $estudiante->exists ? route('estudiantes.show', $estudiante) : route('estudiantes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<form method="POST" action="{{ $estudiante->exists ? route('estudiantes.update', $estudiante) : route('estudiantes.store') }}">
    @csrf
    @if ($estudiante->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-person-vcard me-1"></i> Datos personales</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" name="apellido" id="apellido" maxlength="80" value="{{ $valor('apellido') }}"
                                   class="form-control @error('apellido') is-invalid @enderror" required autofocus>
                            @error('apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" maxlength="80" value="{{ $valor('nombre') }}"
                                   class="form-control @error('nombre') is-invalid @enderror" required>
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="tipo_documento" class="form-label">Tipo de documento</label>
                            <select name="tipo_documento" id="tipo_documento" class="form-select @error('tipo_documento') is-invalid @enderror">
                                @foreach (\App\Models\Estudiante::TIPOS_DOCUMENTO as $v => $t)
                                    <option value="{{ $v }}" @selected($valor('tipo_documento') === $v)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="documento" class="form-label">Número <span class="text-danger">*</span></label>
                            <input type="text" name="documento" id="documento" maxlength="20" value="{{ $valor('documento') }}"
                                   class="form-control @error('documento') is-invalid @enderror" required>
                            @error('documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="cuil" class="form-label">CUIL</label>
                            <input type="text" name="cuil" id="cuil" maxlength="13" value="{{ $valor('cuil') }}"
                                   class="form-control @error('cuil') is-invalid @enderror" placeholder="20-12345678-3">
                            @error('cuil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ $valor('fecha_nacimiento') }}"
                                   class="form-control @error('fecha_nacimiento') is-invalid @enderror">
                            @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="genero" class="form-label">Género</label>
                            <select name="genero" id="genero" class="form-select @error('genero') is-invalid @enderror">
                                <option value="">—</option>
                                @foreach (\App\Models\Estudiante::GENEROS as $v => $t)
                                    <option value="{{ $v }}" @selected($valor('genero') === $v)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="nacionalidad" class="form-label">Nacionalidad <span class="text-danger">*</span></label>
                            <input type="text" name="nacionalidad" id="nacionalidad" maxlength="60" value="{{ $valor('nacionalidad') }}"
                                   class="form-control @error('nacionalidad') is-invalid @enderror" required>
                            @error('nacionalidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="lugar_nacimiento" class="form-label">Lugar de nacimiento</label>
                            <input type="text" name="lugar_nacimiento" id="lugar_nacimiento" maxlength="100" value="{{ $valor('lugar_nacimiento') }}"
                                   class="form-control @error('lugar_nacimiento') is-invalid @enderror">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-house me-1"></i> Domicilio y contacto</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="domicilio" class="form-label">Domicilio</label>
                            <input type="text" name="domicilio" id="domicilio" maxlength="150" value="{{ $valor('domicilio') }}" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label for="localidad" class="form-label">Localidad</label>
                            <input type="text" name="localidad" id="localidad" maxlength="100" value="{{ $valor('localidad') }}" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" maxlength="40" value="{{ $valor('telefono') }}" class="form-control">
                        </div>
                        <div class="col-md-7">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" maxlength="150" value="{{ $valor('email') }}"
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-heart-pulse me-1"></i> Salud y emergencias</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-4">
                            <label for="grupo_sanguineo" class="form-label">Grupo</label>
                            <select name="grupo_sanguineo" id="grupo_sanguineo" class="form-select">
                                <option value="">—</option>
                                @foreach (\App\Models\Estudiante::GRUPOS_SANGUINEOS as $g)
                                    <option value="{{ $g }}" @selected($valor('grupo_sanguineo') === $g)>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-8">
                            <label for="obra_social" class="form-label">Obra social / prepaga</label>
                            <input type="text" name="obra_social" id="obra_social" maxlength="100" value="{{ $valor('obra_social') }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label for="numero_afiliado" class="form-label">N° de afiliado</label>
                            <input type="text" name="numero_afiliado" id="numero_afiliado" maxlength="40" value="{{ $valor('numero_afiliado') }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label for="salud_observaciones" class="form-label">A tener en cuenta</label>
                            <textarea name="salud_observaciones" id="salud_observaciones" rows="3" maxlength="2000" class="form-control"
                                      placeholder="Alergias, medicación, indicaciones médicas…">{{ $valor('salud_observaciones') }}</textarea>
                        </div>
                        <div class="col-md-7">
                            <label for="emergencia_contacto" class="form-label">Contacto de emergencia</label>
                            <input type="text" name="emergencia_contacto" id="emergencia_contacto" maxlength="120" value="{{ $valor('emergencia_contacto') }}" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label for="emergencia_telefono" class="form-label">Teléfono</label>
                            <input type="text" name="emergencia_telefono" id="emergencia_telefono" maxlength="40" value="{{ $valor('emergencia_telefono') }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-folder2 me-1"></i> Legajo</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label for="fecha_ingreso" class="form-label">Fecha de ingreso</label>
                            <input type="date" name="fecha_ingreso" id="fecha_ingreso" value="{{ $valor('fecha_ingreso') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                @foreach (\App\Models\Estudiante::ESTADOS as $v => $t)
                                    <option value="{{ $v }}" @selected($valor('estado') === $v)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="2" maxlength="2000" class="form-control">{{ $valor('observaciones') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-end gap-2">
        <a href="{{ $estudiante->exists ? route('estudiantes.show', $estudiante) : route('estudiantes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        @unless ($estudiante->exists)
            <button class="btn btn-outline-primary" name="despues" value="inscribir"><i class="bi bi-pencil-square me-1"></i> Guardar e inscribir</button>
        @endunless
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
    </div>
</form>

@endsection
