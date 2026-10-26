@extends('layouts.app')

@section('titulo', $usuario->exists ? 'Editar usuario' : 'Nuevo usuario')

@section('contenido')

@php $esUnoMismo = $usuario->exists && $usuario->id === auth()->id(); @endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $usuario->exists ? 'Editar usuario' : 'Nuevo usuario' }}</h1>
        @if ($usuario->exists)
            <p class="text-muted mb-0">{{ $usuario->nombre_completo }}</p>
        @endif
    </div>
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<form method="POST" action="{{ $usuario->exists ? route('usuarios.update', $usuario) : route('usuarios.store') }}">
    @csrf
    @if ($usuario->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-person me-1"></i> Datos personales</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" name="apellido" id="apellido" value="{{ old('apellido', $usuario->apellido) }}"
                                   class="form-control @error('apellido') is-invalid @enderror" maxlength="80" required>
                            @error('apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $usuario->nombre) }}"
                                   class="form-control @error('nombre') is-invalid @enderror" maxlength="80" required>
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-5">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" name="dni" id="dni" value="{{ old('dni', $usuario->dni) }}" inputmode="numeric"
                                   class="form-control @error('dni') is-invalid @enderror" maxlength="10">
                            @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-7">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" maxlength="150">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <div class="form-text mt-0"><i class="bi bi-info-circle"></i> Se ingresa con el email o con el DNI: completá al menos uno.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-shield-lock me-1"></i> Acceso</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="rol" class="form-label">Rol <span class="text-danger">*</span></label>
                        @if ($esUnoMismo)
                            <input type="hidden" name="rol" value="{{ $usuario->rol->value }}">
                            <input type="text" class="form-control" value="{{ $usuario->rol->label() }}" disabled>
                            <div class="form-text">No podés cambiar tu propio rol.</div>
                        @else
                            <select name="rol" id="rol" class="form-select @error('rol') is-invalid @enderror">
                                @foreach ($roles as $valor => $texto)
                                    <option value="{{ $valor }}" @selected(old('rol', $usuario->rol?->value) === $valor)>{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            {{ $usuario->exists ? 'Nueva contraseña' : 'Contraseña inicial' }}
                        </label>
                        <div class="input-group has-validation">
                            <input type="password" name="password" id="password" autocomplete="new-password"
                                   class="form-control @error('password') is-invalid @enderror" minlength="8">
                            <button class="btn btn-outline-secondary" type="button" data-ver-clave="#password"><i class="bi bi-eye"></i></button>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-text">
                            {{ $usuario->exists ? 'Dejala vacía para no cambiarla.' : 'Si la dejás vacía, la contraseña inicial es el DNI.' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Repetir contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control" autocomplete="new-password">
                    </div>

                    @unless ($esUnoMismo)
                        <input type="hidden" name="activo" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="activo" id="activo" value="1"
                                   @checked(old('activo', $usuario->activo))>
                            <label class="form-check-label" for="activo">Usuario habilitado</label>
                        </div>
                    @endunless
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
    </div>
</form>

@endsection
