@extends('layouts.app')

@section('titulo', 'Constancias')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Constancias</h1>
        <p class="text-muted mb-0">Registro de constancias emitidas, numeradas por año y verificables con código QR.</p>
    </div>
    <a href="{{ route('constancias.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Emitir constancia</a>
</div>

<form method="GET" class="card mb-3">
    <div class="card-body row g-2">
        <div class="col-md-5">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Estudiante, DNI, número (15/2026) o código">
        </div>
        <div class="col-md-3">
            <select name="tipo" class="form-select">
                <option value="">Todos los tipos</option>
                @foreach (\App\Models\Constancia::TIPOS as $v => $t)
                    <option value="{{ $v }}" @selected(request('tipo') === $v)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado" class="form-select">
                <option value="">Todas</option>
                <option value="anuladas" @selected(request('estado') === 'anuladas')>Anuladas</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100"><i class="bi bi-search me-1"></i> Buscar</button>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>N°</th>
                    <th>Estudiante</th>
                    <th class="d-none d-md-table-cell">Tipo</th>
                    <th class="d-none d-lg-table-cell">Presentar ante</th>
                    <th class="d-none d-lg-table-cell">Emitida</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($constancias as $c)
                    <tr class="{{ $c->estaAnulada() ? 'text-muted' : '' }}">
                        <td class="text-nowrap fw-semibold">
                            {{ $c->numero . '/' . $c->anio }}
                            @if ($c->estaAnulada()) <span class="badge text-bg-danger" title="{{ $c->motivo_anulacion }}">Anulada</span> @endif
                        </td>
                        <td>
                            {{ $c->datos['estudiante']['apellido'] }}, {{ $c->datos['estudiante']['nombre'] }}
                            <div class="small text-muted">{{ $c->datos['estudiante']['documento'] }}</div>
                        </td>
                        <td class="d-none d-md-table-cell small">
                            {{ $c->tipo_label }}
                            @if ($c->tipo === 'examen') <div class="text-muted">{{ $c->datos['espacio'] }}</div> @endif
                        </td>
                        <td class="d-none d-lg-table-cell small">{{ $c->presentar_ante ?: '—' }}</td>
                        <td class="d-none d-lg-table-cell small text-nowrap">
                            {{ $c->created_at->format('d/m/Y H:i') }}
                            <div class="text-muted">{{ $c->origen === 'portal' ? 'Portal' : $c->emisor?->nombre_completo }}</div>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('constancias.imprimir', [$c, 'volver' => request()->fullUrl()]) }}" class="btn btn-sm btn-outline-primary" title="Ver / imprimir">
                                <i class="bi bi-printer"></i>
                            </a>
                            @if (! $c->estaAnulada() && auth()->user()->rol->esGestion())
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Anular"
                                        data-bs-toggle="modal" data-bs-target="#modalAnular"
                                        data-accion="{{ route('constancias.anular', $c) }}" data-numero="{{ $c->numero_label }}">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay constancias para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($constancias->hasPages())
        <div class="card-footer bg-white">{{ $constancias->links() }}</div>
    @endif
</div>

@include('constancias.partials.modal-anular')

@endsection
