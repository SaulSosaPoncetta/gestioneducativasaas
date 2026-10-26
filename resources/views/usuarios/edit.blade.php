@extends('layouts.app')

@section('titulo', 'Mis datos')

@section('contenido')

<div class="mb-3">
    <h1 class="h3 mb-0">Mis datos</h1>
    <p class="text-muted mb-0">{{ $usuario->rol->label() }}@if ($usuario->institucion) · {{ $usuario->institucion->nombre }}@endif</p>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <form method="POST" action="{{ route('perfil.update') }}" class="card h-100">
            @csrf
            @method('PUT')
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person me-1"></i> Datos personales</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="apellido" class="form-label">Apellido</label>
                        <input type="text" name="apellido" id="apellido" value="{{ old('apellido', $usuario->apellido) }}"
                               class="form-control @error('apellido') is-invalid @enderror" maxlength="80" required>
                        @error('apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $usuario->nombre) }}"
                               class="form-control @error('nombre') is-invalid @enderror" maxlength="80" required>
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}"
                               class="form-control @error('email') is-invalid @enderror" maxlength="150">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">DNI</label>
                        <input type="text" class="form-control" value="{{ $usuario->dni ?: '—' }}" disabled>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar datos</button>
            </div>
        </form>
    </div>

    <div class="col-lg-6">
        <form method="POST" action="{{ route('perfil.password') }}" class="card h-100">
            @csrf
            @method('PUT')
            <div class="card-header bg-white fw-semibold"><i class="bi bi-key me-1"></i> Cambiar contraseña</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="password_actual" class="form-label">Contraseña actual</label>
                    <input type="password" name="password_actual" id="password_actual" autocomplete="current-password"
                           class="form-control @error('password_actual') is-invalid @enderror" required>
                    @error('password_actual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="password_nueva" class="form-label">Nueva contraseña</label>
                    <div class="input-group has-validation">
                        <input type="password" name="password" id="password_nueva" autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror" minlength="8" required>
                        <button class="btn btn-outline-secondary" type="button" data-ver-clave="#password_nueva"><i class="bi bi-eye"></i></button>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-text">Mínimo 8 caracteres.</div>
                </div>
                <div>
                    <label for="password_confirmation" class="form-label">Repetir nueva contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control" autocomplete="new-password" required>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button class="btn btn-primary"><i class="bi bi-key me-1"></i> Cambiar contraseña</button>
            </div>
        </form>
    </div>
</div>

@endsection
