@extends('layouts.app')

@section('titulo', $comunicado->titulo)

@php
    $tipos = ['familia' => ['Familia', 'bi-house-heart'], 'estudiante' => ['Estudiante', 'bi-person'], 'docente' => ['Docente', 'bi-person-workspace']];
@endphp

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <a href="{{ route('comunicados.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Comunicados</a>
        <h1 class="h3 mb-0">{{ $comunicado->titulo }}</h1>
        <p class="text-muted mb-0">
            {{ $comunicado->descripcion_destino }}
            @if ($comunicado->estaPublicado())
                · publicado el {{ $comunicado->publicado_at->format('d/m/Y H:i') }}
            @endif
        </p>
    </div>

    @unless ($comunicado->estaPublicado())
        <div class="d-flex gap-2">
            <a href="{{ route('comunicados.edit', $comunicado) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i> Editar</a>
            <form method="POST" action="{{ route('comunicados.destroy', $comunicado) }}" data-confirmar="¿Eliminar este borrador?">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
        </div>
    @endunless
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if ($comunicado->estaPublicado())
                        <span class="badge text-bg-success">Publicado</span>
                    @else
                        <span class="badge text-bg-warning">Borrador</span>
                    @endif
                    @if ($comunicado->requiere_confirmacion) <span class="badge text-bg-light border">Pide confirmación</span> @endif
                    @if ($comunicado->enviar_email) <span class="badge text-bg-light border"><i class="bi bi-envelope"></i> Con email</span> @endif
                </div>
                <div class="comunicado-cuerpo">{!! $comunicado->cuerpoHtml() !!}</div>

                @if ($comunicado->adjunto_path)
                    <a href="{{ route('comunicados.adjunto', $comunicado) }}" class="btn btn-sm btn-outline-secondary mt-3">
                        <i class="bi bi-paperclip me-1"></i>{{ $comunicado->adjunto_nombre }}
                    </a>
                @endif
                <p class="small text-muted mt-3 mb-0">Por {{ $comunicado->autor?->nombre_completo }}</p>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        @if ($comunicado->estaPublicado())
            <div class="card">
                <div class="card-body">
                    <h2 class="h6">Seguimiento</h2>
                    <div class="row text-center g-2">
                        <div class="col"><div class="fs-4 fw-semibold">{{ $totales['total'] }}</div><div class="small text-muted">Destinatarios</div></div>
                        <div class="col"><div class="fs-4 fw-semibold">{{ $totales['leidos'] }}</div><div class="small text-muted">Leyeron</div></div>
                        @if ($comunicado->requiere_confirmacion)
                            <div class="col"><div class="fs-4 fw-semibold">{{ $totales['confirmados'] }}</div><div class="small text-muted">Confirmaron</div></div>
                        @endif
                        @if ($comunicado->enviar_email)
                            <div class="col"><div class="fs-4 fw-semibold">{{ $totales['emails'] }}</div><div class="small text-muted">Emails</div></div>
                        @endif
                    </div>
                    @php $pct = $totales['total'] ? round($totales['leidos'] * 100 / $totales['total']) : 0; @endphp
                    <div class="progress mt-3" style="height: 8px"><div class="progress-bar" style="width: {{ $pct }}%"></div></div>
                    <div class="small text-muted mt-1">{{ $pct }}% leído</div>
                </div>
            </div>
        @else
            <div class="card border-primary">
                <div class="card-body">
                    <h2 class="h6">Antes de publicar</h2>
                    @php $cant = $previa['destinatarios']->count(); @endphp
                    <p class="mb-1">
                        Llega a <strong>{{ $cant }} {{ $cant === 1 ? 'persona' : 'personas' }}</strong> con acceso al portal.
                    </p>
                    @if ($previa['sin_cuenta'])
                        <p class="small text-muted">
                            <i class="bi bi-info-circle"></i>
                            {{ $previa['sin_cuenta'] }} {{ $previa['sin_cuenta'] === 1 ? 'destinatario no tiene' : 'destinatarios no tienen' }} usuario todavía.
                            Podés generar los accesos desde Estudiantes → Accesos a portales.
                        </p>
                    @endif
                    @php
                        $porTipo = $previa['destinatarios']->groupBy('tipo')->map->count();
                    @endphp
                    @if ($porTipo->isNotEmpty())
                        <ul class="small mb-3">
                            @foreach ($porTipo as $t => $n)
                                <li>{{ $tipos[$t][0] ?? $t }}: {{ $n }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <form method="POST" action="{{ route('comunicados.publicar', $comunicado) }}"
                          data-confirmar="¿Publicar el comunicado? Ya no se va a poder editar.">
                        @csrf
                        <button class="btn btn-primary w-100" @disabled($cant === 0)>
                            <i class="bi bi-send me-1"></i> Publicar{{ $comunicado->enviar_email ? ' y enviar emails' : '' }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

@if ($comunicado->estaPublicado())
    <div class="card mt-3">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <span class="fw-semibold">Destinatarios</span>
            <div class="btn-group btn-group-sm">
                @php
                    $filtros = ['' => 'Todos', 'sin_leer' => 'Sin leer', 'leidos' => 'Leídos'];
                    if ($comunicado->requiere_confirmacion) $filtros['sin_confirmar'] = 'Sin confirmar';
                @endphp
                @foreach ($filtros as $v => $t)
                    <a href="{{ route('comunicados.show', array_filter([$comunicado->id, 'filtro' => $v])) }}"
                       class="btn {{ (string) request('filtro') === $v ? 'btn-primary' : 'btn-outline-primary' }}">{{ $t }}</a>
                @endforeach
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Persona</th>
                        <th class="d-none d-md-table-cell">Referencia</th>
                        <th class="text-center">Leído</th>
                        @if ($comunicado->requiere_confirmacion) <th class="text-center">Confirmado</th> @endif
                        @if ($comunicado->enviar_email) <th class="text-center d-none d-sm-table-cell">Email</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($destinatarios as $d)
                        <tr>
                            <td>
                                <i class="bi {{ $tipos[$d->tipo][1] ?? 'bi-person' }} text-muted me-1" title="{{ $tipos[$d->tipo][0] ?? '' }}"></i>
                                {{ $d->usuario?->apellido }}, {{ $d->usuario?->nombre }}
                            </td>
                            <td class="d-none d-md-table-cell small text-muted">{{ $d->referencia }}</td>
                            <td class="text-center small">
                                @if ($d->leido_at)
                                    <i class="bi bi-check2-all text-success"></i> {{ $d->leido_at->format('d/m H:i') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            @if ($comunicado->requiere_confirmacion)
                                <td class="text-center small">
                                    @if ($d->confirmado_at)
                                        <i class="bi bi-patch-check text-success"></i> {{ $d->confirmado_at->format('d/m H:i') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endif
                            @if ($comunicado->enviar_email)
                                <td class="text-center d-none d-sm-table-cell">
                                    @if ($d->email_enviado_at)
                                        <i class="bi bi-envelope-check text-success" title="{{ $d->email_enviado_at->format('d/m H:i') }}"></i>
                                    @else
                                        <i class="bi bi-envelope-dash text-muted" title="{{ $d->usuario?->email ? 'Pendiente' : 'Sin email' }}"></i>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No hay destinatarios en este filtro.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($destinatarios->hasPages())
            <div class="card-footer bg-white">{{ $destinatarios->links() }}</div>
        @endif
    </div>
@endif

@endsection
