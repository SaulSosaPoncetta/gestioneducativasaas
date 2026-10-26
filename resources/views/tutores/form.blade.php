@extends('layouts.app')

@php
    $conEstudiante = $estudiante !== null;
    $editando      = $tutor->exists && $conEstudiante && $estudiante->tutores->contains($tutor->id);
    $volver        = $conEstudiante ? route('estudiantes.show', $estudiante) : route('tutores.index');

    $accion = match (true) {
        ! $conEstudiante => route('tutores.update', $tutor),
        $editando        => route('estudiantes.tutores.update', [$estudiante, $tutor]),
        default          => route('estudiantes.tutores.store', $estudiante),
    };
@endphp

@section('titulo', $editando || ! $conEstudiante ? 'Editar tutor' : 'Agregar tutor')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $editando || ! $conEstudiante ? 'Editar ' . $tutor->nombre_completo : 'Agregar tutor o responsable' }}</h1>
        @if ($conEstudiante) <p class="text-muted mb-0">De {{ $estudiante->nombre_completo }}</p> @endif
    </div>
    <a href="{{ $volver }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

{{-- Paso 1 (alta): buscar por DNI para no duplicar tutores (hermanos) --}}
@if ($conEstudiante && ! $editando)
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('estudiantes.tutores.create', $estudiante) }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="buscar_documento" class="form-label">DNI del tutor</label>
                    <input type="text" name="documento" id="buscar_documento" inputmode="numeric" value="{{ request('documento') }}"
                           class="form-control" placeholder="Buscar si ya está cargado" {{ $buscado ? '' : 'autofocus' }}>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary w-100"><i class="bi bi-search me-1"></i> Buscar</button>
                </div>
                <div class="col-12 small">
                    @if ($buscado && $tutor->exists)
                        <span class="text-success"><i class="bi bi-check-circle"></i> Ya está cargado
                            @if ($tutor->estudiantes->isNotEmpty())
                                (responsable de {{ $tutor->estudiantes->pluck('nombre_completo')->join(', ') }})
                            @endif.
                            Completá el vínculo y guardá.
                        </span>
                    @elseif ($buscado)
                        <span class="text-muted"><i class="bi bi-info-circle"></i> No hay ningún tutor con ese DNI: cargá sus datos.</span>
                    @else
                        <span class="text-muted">Si es madre, padre o tutor de otro estudiante de la institución, al buscarlo se usan sus datos.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endif

@if (! $conEstudiante || $editando || $buscado)
<form method="POST" action="{{ $accion }}">
    @csrf
    @if (! $conEstudiante || $editando) @method('PUT') @endif
    @if ($tutor->exists && ! $editando && $conEstudiante)
        <input type="hidden" name="tutor_id" value="{{ $tutor->id }}">
    @endif

    <div class="row g-3">
        <div class="{{ $conEstudiante ? 'col-xl-7' : 'col-xl-8' }}">
            <div class="card">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-person me-1"></i> Datos del tutor</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" name="apellido" id="apellido" maxlength="80" value="{{ old('apellido', $tutor->apellido) }}"
                                   class="form-control @error('apellido') is-invalid @enderror" required>
                            @error('apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" maxlength="80" value="{{ old('nombre', $tutor->nombre) }}"
                                   class="form-control @error('nombre') is-invalid @enderror" required>
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="documento" class="form-label">DNI <span class="text-danger">*</span></label>
                            <input type="text" name="documento" id="documento" inputmode="numeric" maxlength="10"
                                   value="{{ old('documento', $tutor->documento) }}"
                                   class="form-control @error('documento') is-invalid @enderror" required>
                            @error('documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" maxlength="40" value="{{ old('telefono', $tutor->telefono) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="telefono_alternativo" class="form-label">Teléfono alternativo</label>
                            <input type="text" name="telefono_alternativo" id="telefono_alternativo" maxlength="40"
                                   value="{{ old('telefono_alternativo', $tutor->telefono_alternativo) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" maxlength="150" value="{{ old('email', $tutor->email) }}"
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="ocupacion" class="form-label">Ocupación</label>
                            <input type="text" name="ocupacion" id="ocupacion" maxlength="100" value="{{ old('ocupacion', $tutor->ocupacion) }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label for="domicilio" class="form-label">Domicilio</label>
                            <input type="text" name="domicilio" id="domicilio" maxlength="150" value="{{ old('domicilio', $tutor->domicilio) }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($conEstudiante)
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-link-45deg me-1"></i> Vínculo con {{ $estudiante->nombre }}</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="vinculo" class="form-label">Vínculo <span class="text-danger">*</span></label>
                            <select name="vinculo" id="vinculo" class="form-select @error('vinculo') is-invalid @enderror" required>
                                <option value="">Elegí…</option>
                                @foreach (\App\Models\Tutor::VINCULOS as $v => $t)
                                    <option value="{{ $v }}" @selected(old('vinculo', $vinculo->vinculo) === $v)>{{ $t }}</option>
                                @endforeach
                            </select>
                            @error('vinculo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @foreach ([
                            'es_responsable'        => 'Responsable principal',
                            'autorizado_retirar'    => 'Autorizado a retirar al estudiante',
                            'convive'               => 'Convive con el estudiante',
                            'recibe_notificaciones' => 'Recibe comunicaciones y avisos',
                        ] as $campo => $texto)
                            <input type="hidden" name="{{ $campo }}" value="0">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" name="{{ $campo }}" id="{{ $campo }}" value="1"
                                       @checked(old($campo, $vinculo->{$campo}))>
                                <label class="form-check-label" for="{{ $campo }}">{{ $texto }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ $volver }}" class="btn btn-outline-secondary">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
    </div>
</form>
@endif

@endsection
