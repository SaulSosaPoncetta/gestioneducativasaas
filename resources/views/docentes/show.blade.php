@extends('layouts.app')

@section('titulo', $docente->nombre_completo)

@section('contenido')

@php $dato = fn ($v) => filled($v) ? e($v) : '<span class="text-muted">—</span>'; @endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div class="d-flex align-items-center gap-3">
        <span class="avatar" style="width:56px;height:56px;font-size:1.2rem">{{ mb_substr($docente->nombre, 0, 1) }}{{ mb_substr($docente->apellido, 0, 1) }}</span>
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h1 class="h3 mb-0">{{ $docente->nombre_completo }}</h1>
                <span class="badge text-bg-{{ $docente->estado_color }}">{{ $docente->estado_label }}</span>
            </div>
            <div class="text-muted">{{ $docente->documento_label }} @if ($docente->titulo) · {{ $docente->titulo }} @endif</div>
            <div class="small">
                @if ($docente->usuario)
                    <span class="text-success"><i class="bi bi-key"></i> Con acceso al portal docente</span>
                @else
                    <span class="text-muted"><i class="bi bi-key"></i> Sin usuario</span>
                @endif
            </div>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('docentes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <a href="{{ route('docentes.edit', $docente) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Editar</a>
        <a href="{{ route('asignaciones.create', ['docente' => $docente->id]) }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Asignar espacios</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-vcard me-1"></i> Datos</div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted fw-normal">CUIL</dt><dd class="col-7">{!! $dato($docente->cuil) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Nacimiento</dt><dd class="col-7">{!! $dato($docente->fecha_nacimiento?->format('d/m/Y')) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Teléfono</dt><dd class="col-7">{!! $dato($docente->telefono) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Email</dt><dd class="col-7">{!! $dato($docente->email) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Domicilio</dt><dd class="col-7">{!! $dato(trim($docente->domicilio . ($docente->localidad ? ', ' . $docente->localidad : ''), ', ')) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Legajo / ficha</dt><dd class="col-7">{!! $dato($docente->legajo) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Ingreso</dt><dd class="col-7 mb-0">{!! $dato($docente->fecha_ingreso?->format('d/m/Y')) !!}</dd>
                </dl>
                @if ($docente->observaciones)
                    <hr><p class="small mb-0">{{ $docente->observaciones }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        @forelse ($asignaciones as $lista)
            @php $ciclo = $lista->first()->ciclo; $activas = $lista->where('estado', 'activa'); @endphp
            <div class="card mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-calendar3 me-1"></i> {{ $ciclo->nombre }}</span>
                    <span class="small text-muted">{{ $activas->count() }} espacios activos · {{ $activas->sum('horas') }} hs cátedra</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Espacio curricular</th>
                                <th>Curso / comisión</th>
                                <th class="d-none d-md-table-cell">Situación</th>
                                <th class="text-center d-none d-sm-table-cell">Hs</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lista as $a)
                                <tr @class(['text-muted' => ! $a->estaActiva()])>
                                    <td>
                                        {{ $a->espacio->nombre }}
                                        <div class="small text-muted">{{ $a->espacio->plan->nombre }}</div>
                                    </td>
                                    <td>{{ $a->grupo }}</td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge text-bg-{{ $a->situacion_color }}">{{ $a->situacion_label }}</span>
                                        @unless ($a->estaActiva())
                                            <span class="badge text-bg-secondary">Finalizada {{ $a->fecha_hasta?->format('d/m/Y') }}</span>
                                        @endunless
                                    </td>
                                    <td class="text-center d-none d-sm-table-cell">{{ $a->horas ?: '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('asignaciones.edit', $a) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    Todavía no tiene espacios asignados.
                    <div class="mt-2"><a href="{{ route('asignaciones.create', ['docente' => $docente->id]) }}" class="btn btn-sm btn-primary">Asignar espacios</a></div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@endsection
