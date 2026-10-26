@extends('layouts.app')

@section('titulo', $estudiante->nombre_completo)

@section('contenido')

@php
    $puedeEditar = auth()->user()->rol->esGestion();
    $dato = fn ($valor) => filled($valor) ? e($valor) : '<span class="text-muted">—</span>';
    $activas = $estudiante->inscripciones->where('estado', 'activa');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div class="d-flex align-items-center gap-3">
        <span class="avatar" style="width:56px;height:56px;font-size:1.2rem">{{ mb_substr($estudiante->nombre, 0, 1) }}{{ mb_substr($estudiante->apellido, 0, 1) }}</span>
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h1 class="h3 mb-0">{{ $estudiante->nombre_completo }}</h1>
                <span class="badge text-bg-{{ $estudiante->estado_color }}">{{ $estudiante->estado_label }}</span>
            </div>
            <div class="text-muted">
                Legajo N° {{ $estudiante->legajo }} · {{ $estudiante->documento_label }}
                @if ($estudiante->edad !== null) · {{ $estudiante->edad }} años @endif
            </div>
            @foreach ($activas as $i)
                <span class="badge text-bg-primary mt-1"><i class="bi bi-mortarboard me-1"></i>{{ $i->destino }} · {{ $i->ciclo->anio }}</span>
            @endforeach
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('estudiantes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        @if ($puedeEditar)
            <a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Editar</a>
            <a href="{{ route('estudiantes.inscripciones.create', $estudiante) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i> Inscribir</a>
        @endif
    </div>
</div>

@if (filled($estudiante->salud_observaciones))
    <div class="alert alert-warning d-flex gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div><strong>Salud:</strong> {{ $estudiante->salud_observaciones }}</div>
    </div>
@endif

<div class="row g-3">
    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-vcard me-1"></i> Datos personales</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Fecha de nacimiento</dt>
                    <dd class="col-7">{!! $dato($estudiante->fecha_nacimiento?->format('d/m/Y')) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Género</dt>
                    <dd class="col-7">{!! $dato(\App\Models\Estudiante::GENEROS[$estudiante->genero] ?? null) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">CUIL</dt>
                    <dd class="col-7">{!! $dato($estudiante->cuil) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Nacionalidad</dt>
                    <dd class="col-7">{!! $dato($estudiante->nacionalidad) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Lugar de nacimiento</dt>
                    <dd class="col-7">{!! $dato($estudiante->lugar_nacimiento) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Domicilio</dt>
                    <dd class="col-7">{!! $dato(trim($estudiante->domicilio . ($estudiante->localidad ? ', ' . $estudiante->localidad : ''), ', ')) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Teléfono</dt>
                    <dd class="col-7">{!! $dato($estudiante->telefono) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Email</dt>
                    <dd class="col-7">{!! $dato($estudiante->email) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Ingreso</dt>
                    <dd class="col-7 mb-0">{!! $dato($estudiante->fecha_ingreso?->format('d/m/Y')) !!}</dd>
                </dl>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-heart-pulse me-1"></i> Salud y emergencias</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Grupo sanguíneo</dt>
                    <dd class="col-7">{!! $dato($estudiante->grupo_sanguineo) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Obra social</dt>
                    <dd class="col-7">{!! $dato(trim($estudiante->obra_social . ($estudiante->numero_afiliado ? ' · N° ' . $estudiante->numero_afiliado : ''))) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Emergencias</dt>
                    <dd class="col-7 mb-0">{!! $dato(trim($estudiante->emergencia_contacto . ($estudiante->emergencia_telefono ? ' · ' . $estudiante->emergencia_telefono : ''))) !!}</dd>
                </dl>
            </div>
        </div>

        @if (filled($estudiante->observaciones))
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-chat-left-text me-1"></i> Observaciones</div>
                <div class="card-body small">{{ $estudiante->observaciones }}</div>
            </div>
        @endif
    </div>

    <div class="col-xl-7">
        {{-- Familia --}}
        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-house-heart me-1"></i> Familia y responsables</span>
                @if ($puedeEditar)
                    <a href="{{ route('estudiantes.tutores.create', $estudiante) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg"></i> Agregar</a>
                @endif
            </div>
            @if ($estudiante->tutores->isEmpty())
                <div class="card-body small text-muted">No hay tutores cargados.</div>
            @else
                <div class="list-group list-group-flush">
                    @foreach ($estudiante->tutores as $t)
                        <div class="list-group-item">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div>
                                    <div class="fw-semibold">
                                        {{ $t->nombre_completo }}
                                        <span class="text-muted fw-normal">· {{ \App\Models\Tutor::vinculoLabel($t->pivot->vinculo) }}</span>
                                        @if ($t->pivot->es_responsable) <span class="badge text-bg-primary ms-1">Responsable</span> @endif
                                    </div>
                                    <div class="small text-muted">
                                        DNI {{ number_format((int) $t->documento, 0, ',', '.') }}
                                        @if ($t->telefono) · <i class="bi bi-telephone"></i> {{ $t->telefono }} @endif
                                        @if ($t->email) · <i class="bi bi-envelope"></i> {{ $t->email }} @endif
                                    </div>
                                    <div class="small mt-1 d-flex flex-wrap gap-2">
                                        <span class="{{ $t->pivot->autorizado_retirar ? 'text-success' : 'text-danger' }}">
                                            <i class="bi {{ $t->pivot->autorizado_retirar ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                            {{ $t->pivot->autorizado_retirar ? 'Autorizado a retirar' : 'NO autorizado a retirar' }}
                                        </span>
                                        @if ($t->pivot->convive) <span class="text-muted"><i class="bi bi-house"></i> Convive</span> @endif
                                        @if ($t->pivot->recibe_notificaciones) <span class="text-muted"><i class="bi bi-bell"></i> Recibe avisos</span> @endif
                                    </div>
                                </div>
                                @if ($puedeEditar)
                                    <div class="text-nowrap">
                                        <a href="{{ route('estudiantes.tutores.edit', [$estudiante, $t]) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('estudiantes.tutores.destroy', [$estudiante, $t]) }}" class="d-inline"
                                              data-confirmar="¿Desvincular a {{ $t->nombre_completo }} de este estudiante?">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Desvincular"><i class="bi bi-link-45deg"></i></button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Inscripciones --}}
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-journal-check me-1"></i> Inscripciones</div>
            @if ($estudiante->inscripciones->isEmpty())
                <div class="card-body small text-muted">Todavía no tiene inscripciones.</div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ciclo</th>
                                <th>Curso / carrera</th>
                                <th class="d-none d-md-table-cell">Condición</th>
                                <th class="text-center">Estado</th>
                                @if ($puedeEditar) <th class="text-end"></th> @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estudiante->inscripciones->sortByDesc(fn ($i) => $i->ciclo->anio) as $i)
                                <tr>
                                    <td>{{ $i->ciclo->anio }}</td>
                                    <td>
                                        <div>{{ $i->destino }}</div>
                                        <div class="small text-muted">
                                            {{ $i->nivel->nivel->labelCorto() }}
                                            @if ($i->curso && $i->plan) · {{ $i->plan->nombre }} @endif
                                            · desde {{ $i->fecha->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell small">{{ $i->condicion_label }}</td>
                                    <td class="text-center">
                                        <span class="badge text-bg-{{ $i->estado_color }}">{{ $i->estado_label }}</span>
                                        @if ($i->fecha_baja) <div class="small text-muted">{{ $i->fecha_baja->format('d/m/Y') }}</div> @endif
                                    </td>
                                    @if ($puedeEditar)
                                        <td class="text-end">
                                            <a href="{{ route('inscripciones.edit', $i) }}" class="btn btn-sm btn-outline-secondary" title="Gestionar"><i class="bi bi-pencil"></i></a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
