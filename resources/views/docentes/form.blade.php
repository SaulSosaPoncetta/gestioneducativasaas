@extends('layouts.app')

@section('titulo', $docente->exists ? 'Editar docente' : 'Nuevo docente')

@section('contenido')

@php
    $valor = fn ($c) => old($c, $docente->{$c} instanceof \Carbon\CarbonInterface ? $docente->{$c}->format('Y-m-d') : $docente->{$c});
    $volver = $docente->exists ? route('docentes.show', $docente) : route('docentes.index');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">{{ $docente->exists ? 'Editar ' . $docente->nombre_completo : 'Nuevo docente' }}</h1>
    <a href="{{ $volver }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<form method="POST" action="{{ $docente->exists ? route('docentes.update', $docente) : route('docentes.store') }}">
    @csrf
    @if ($docente->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card">
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
                            <label for="documento" class="form-label">DNI <span class="text-danger">*</span></label>
                            <input type="text" name="documento" id="documento" inputmode="numeric" maxlength="10" value="{{ $valor('documento') }}"
                                   class="form-control @error('documento') is-invalid @enderror" required>
                            @error('documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="cuil" class="form-label">CUIL</label>
                            <input type="text" name="cuil" id="cuil" maxlength="13" value="{{ $valor('cuil') }}"
                                   class="form-control @error('cuil') is-invalid @enderror">
                            @error('cuil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ $valor('fecha_nacimiento') }}"
                                   class="form-control @error('fecha_nacimiento') is-invalid @enderror">
                            @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <div class="col-md-7">
                            <label for="domicilio" class="form-label">Domicilio</label>
                            <input type="text" name="domicilio" id="domicilio" maxlength="150" value="{{ $valor('domicilio') }}" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label for="localidad" class="form-label">Localidad</label>
                            <input type="text" name="localidad" id="localidad" maxlength="100" value="{{ $valor('localidad') }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-mortarboard me-1"></i> Datos laborales</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" name="titulo" id="titulo" maxlength="200" value="{{ $valor('titulo') }}" class="form-control"
                                   placeholder="Ej: Profesora de Matemática">
                        </div>
                        <div class="col-6">
                            <label for="legajo" class="form-label">Legajo / ficha</label>
                            <input type="text" name="legajo" id="legajo" maxlength="30" value="{{ $valor('legajo') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="fecha_ingreso" class="form-label">Ingreso</label>
                            <input type="date" name="fecha_ingreso" id="fecha_ingreso" value="{{ $valor('fecha_ingreso') }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                @foreach (\App\Models\Docente::ESTADOS as $v => $t)
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

            <div class="card">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-key me-1"></i> Acceso al sistema</div>
                <div class="card-body">
                    @if ($docente->usuario)
                        <p class="small mb-0">
                            <i class="bi bi-check-circle text-success"></i>
                            Tiene usuario ({{ $docente->usuario->rol->label() }}). Ingresa con
                            {{ $docente->usuario->email ? 'su email o ' : '' }}su DNI.
                        </p>
                    @else
                        <input type="hidden" name="crear_usuario" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="crear_usuario" id="crear_usuario" value="1"
                                   @checked(old('crear_usuario', ! $docente->exists))>
                            <label class="form-check-label" for="crear_usuario">Crear usuario para el portal docente</label>
                        </div>
                        <div class="form-text">Ingresa con su DNI (o email). La contraseña inicial es el DNI. Si ya existe un usuario con ese DNI, se vincula.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ $volver }}" class="btn btn-outline-secondary">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
    </div>
</form>

@endsection
