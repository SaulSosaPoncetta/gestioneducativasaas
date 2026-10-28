@extends('layouts.app')

@section('titulo', 'Auditoría')

@php use App\Support\Auditoria\Formato; @endphp

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Auditoría</h1>
        <p class="text-muted mb-0">Quién cambió qué y cuándo: altas, modificaciones, bajas e ingresos al sistema.</p>
    </div>
    <a href="{{ route('auditoria.exportar', request()->query()) }}" class="btn btn-outline-secondary"><i class="bi bi-filetype-csv me-1"></i> Exportar CSV</a>
</div>

@if (request()->filled('id'))
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-1"></i> Historial de un registro ({{ $modelos[request('modelo')] ?? request('modelo') }} #{{ request('id') }}).</span>
        <a href="{{ route('auditoria.index') }}" class="btn btn-sm btn-outline-primary">Ver todo</a>
    </div>
@endif

<form method="GET" class="card mb-3">
    <div class="card-body row g-2">
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1" for="desde">Desde</label>
            <input type="date" name="desde" id="desde" value="{{ request('desde') }}" class="form-control">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1" for="hasta">Hasta</label>
            <input type="date" name="hasta" id="hasta" value="{{ request('hasta') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="usuario">Usuario</label>
            <select name="usuario" id="usuario" class="form-select">
                <option value="">Todos</option>
                @foreach ($usuarios as $u)
                    <option value="{{ $u->id }}" @selected((int) request('usuario') === $u->id)>{{ $u->apellido }}, {{ $u->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="evento">Evento</label>
            <select name="evento" id="evento" class="form-select">
                <option value="">Todos</option>
                @foreach (\App\Models\Auditoria::EVENTOS as $v => [$t])
                    <option value="{{ $v }}" @selected(request('evento') === $v)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="modelo">Sobre</label>
            <select name="modelo" id="modelo" class="form-select">
                <option value="">Todo</option>
                @foreach ($modelos as $v => $t)
                    <option value="{{ $v }}" @selected(request('modelo') === $v)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="q">Texto</label>
            <input type="search" name="q" id="q" value="{{ request('q') }}" class="form-control" placeholder="Ej.: Romero">
        </div>
        @if (request()->filled('id')) <input type="hidden" name="id" value="{{ request('id') }}"> @endif
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filtrar</button>
            <a href="{{ route('auditoria.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 140px">Fecha</th>
                    <th>Usuario</th>
                    <th>Evento</th>
                    <th>Registro</th>
                    <th class="d-none d-lg-table-cell">Origen</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($registros as $r)
                    @php $cambios = $r->cambios(); @endphp
                    <tr>
                        <td class="small text-nowrap">{{ $r->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="small">{{ $r->usuario_nombre ?? '—' }}</td>
                        <td><span class="badge text-bg-{{ $r->evento_color }}">{{ $r->evento_label }}</span></td>
                        <td class="small">
                            {{ $r->descripcion }}
                            @if ($r->evento === 'actualizado' && $cambios)
                                <div class="text-muted">{{ collect(array_keys($cambios))->map(fn ($c) => Formato::campo($c))->join(', ') }}</div>
                            @endif
                        </td>
                        <td class="d-none d-lg-table-cell small text-muted text-nowrap">
                            {{ ['web' => 'Web', 'api' => 'API', 'consola' => 'Consola'][$r->origen] ?? $r->origen }}
                            @if ($r->ip) · {{ $r->ip }} @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if ($cambios)
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#det{{ $r->id }}" aria-expanded="false" title="Ver detalle">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            @endif
                            @if ($r->modelo_tipo && $r->modelo_id && ! request()->filled('id'))
                                <a href="{{ route('auditoria.index', ['modelo' => class_basename($r->modelo_tipo), 'id' => $r->modelo_id]) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Historial de este registro"><i class="bi bi-clock-history"></i></a>
                            @endif
                        </td>
                    </tr>
                    @if ($cambios)
                        <tr class="collapse" id="det{{ $r->id }}">
                            <td colspan="6" class="bg-body-tertiary">
                                <table class="table table-sm table-bordered bg-white mb-0 small">
                                    <thead>
                                        <tr>
                                            <th style="width: 25%">Campo</th>
                                            @if ($r->evento !== 'creado') <th>Antes</th> @endif
                                            @if ($r->evento !== 'eliminado') <th>{{ $r->evento === 'creado' ? 'Valor' : 'Después' }}</th> @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cambios as $campo => $c)
                                            <tr>
                                                <td class="fw-semibold">{{ Formato::campo($campo) }}</td>
                                                @if ($r->evento !== 'creado') <td class="text-danger-emphasis text-break">{{ Formato::valor($c['antes']) }}</td> @endif
                                                @if ($r->evento !== 'eliminado') <td class="text-success-emphasis text-break">{{ Formato::valor($c['despues']) }}</td> @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if ($r->url) <div class="small text-muted mt-1">{{ $r->url }}</div> @endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay registros para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($registros->hasPages())
        <div class="card-footer bg-white">{{ $registros->links() }}</div>
    @endif
</div>

@endsection
