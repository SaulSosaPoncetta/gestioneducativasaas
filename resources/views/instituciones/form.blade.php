@extends('layouts.app')

@section('titulo', $institucion->exists ? 'Editar institución' : 'Nueva institución')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $institucion->exists ? 'Editar institución' : 'Nueva institución' }}</h1>
        @if ($institucion->exists)
            <p class="text-muted mb-0">{{ $institucion->nombre }}</p>
        @endif
    </div>
    <a href="{{ route('instituciones.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<form method="POST" enctype="multipart/form-data"
      action="{{ $institucion->exists ? route('instituciones.update', $institucion) : route('instituciones.store') }}">
    @csrf
    @if ($institucion->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-building me-1"></i> Datos de la institución</div>
                <div class="card-body">
                    @include('instituciones.partials.campos')
                </div>
            </div>

            @unless ($institucion->exists)
                <div class="card mb-3">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-person-gear me-1"></i> Administrador de la institución</div>
                    <div class="card-body">
                        <p class="small text-muted">Es el primer usuario: después da de alta al resto del personal.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="admin[nombre]" value="{{ old('admin.nombre') }}"
                                       class="form-control @error('admin.nombre') is-invalid @enderror" maxlength="80">
                                @error('admin.nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellido <span class="text-danger">*</span></label>
                                <input type="text" name="admin[apellido]" value="{{ old('admin.apellido') }}"
                                       class="form-control @error('admin.apellido') is-invalid @enderror" maxlength="80">
                                @error('admin.apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="admin[email]" value="{{ old('admin.email') }}"
                                       class="form-control @error('admin.email') is-invalid @enderror" maxlength="150">
                                @error('admin.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">DNI</label>
                                <input type="text" name="admin[dni]" value="{{ old('admin.dni') }}" inputmode="numeric"
                                       class="form-control @error('admin.dni') is-invalid @enderror" maxlength="10">
                                @error('admin.dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contraseña inicial <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="password" name="admin[password]" id="admin_password"
                                           class="form-control @error('admin.password') is-invalid @enderror" minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" data-ver-clave="#admin_password"><i class="bi bi-eye"></i></button>
                                    @error('admin.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-text">Mínimo 8 caracteres. Puede cambiarla desde "Mis datos".</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endunless
        </div>

        <div class="col-xl-5">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-layers me-1"></i> Niveles que ofrece</div>
                <div class="card-body">
                    @include('instituciones.partials.niveles')
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <input type="hidden" name="activo" value="0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="activo" id="activo" value="1"
                               @checked(old('activo', $institucion->activo))>
                        <label class="form-check-label" for="activo">Institución habilitada</label>
                    </div>
                    <div class="form-text">Si se deshabilita, ninguno de sus usuarios puede ingresar.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('instituciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
    </div>
</form>

@endsection
