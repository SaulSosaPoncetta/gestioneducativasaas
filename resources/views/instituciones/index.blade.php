@extends('layouts.app')

@section('titulo', 'Instituciones')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Instituciones</h1>
        <p class="text-muted mb-0">Clientes de Gestión Educativa.</p>
    </div>
    <a href="{{ route('instituciones.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nueva institución
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small text-muted mb-1">Buscar</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nombre, CUE o localidad">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todas</option>
                    <option value="activas" @selected(request('estado') === 'activas')>Activas</option>
                    <option value="inactivas" @selected(request('estado') === 'inactivas')>Inactivas</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary flex-grow-1"><i class="bi bi-search me-1"></i> Filtrar</button>
                @if (request()->hasAny(['q', 'estado']))
                    <a href="{{ route('instituciones.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Institución</th>
                    <th class="d-none d-md-table-cell">Ubicación</th>
                    <th>Niveles</th>
                    <th class="text-center d-none d-sm-table-cell">Usuarios</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($instituciones as $inst)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $inst->nombre }}</div>
                            <div class="small text-muted">
                                {{ ucfirst($inst->tipo_gestion) }}
                                @if ($inst->cue) · CUE {{ $inst->cue }} @endif
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell small">
                            {{ $inst->localidad ?: '—' }}<br>
                            <span class="text-muted">{{ $inst->provincia }}</span>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach ($inst->niveles->where('activo', true) as $n)
                                    <span class="badge rounded-pill text-bg-light border" title="{{ $n->nivel->label() }}">
                                        <i class="bi {{ $n->nivel->icono() }}"></i> {{ $n->nivel->labelCorto() }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="text-center d-none d-sm-table-cell">{{ $inst->usuarios_count }}</td>
                        <td class="text-center">
                            <span class="badge {{ $inst->activo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $inst->activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <form method="POST" action="{{ route('institucion.entrar', $inst) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary" title="Entrar a la institución">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </button>
                            </form>
                            <a href="{{ route('instituciones.edit', $inst) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('instituciones.estado', $inst) }}" class="d-inline"
                                  data-confirmar="{{ $inst->activo ? '¿Deshabilitar la institución? Sus usuarios no van a poder ingresar.' : '¿Habilitar la institución?' }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm {{ $inst->activo ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                        title="{{ $inst->activo ? 'Deshabilitar' : 'Habilitar' }}">
                                    <i class="bi {{ $inst->activo ? 'bi-slash-circle' : 'bi-check-circle' }}"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No hay instituciones para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($instituciones->hasPages())
        <div class="card-footer bg-white">{{ $instituciones->links() }}</div>
    @endif
</div>

@endsection
