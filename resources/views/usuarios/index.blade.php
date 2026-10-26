@extends('layouts.app')

@section('titulo', 'Usuarios')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Usuarios</h1>
        <p class="text-muted mb-0">Cuentas de acceso al sistema y sus roles.</p>
    </div>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> Nuevo usuario
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Buscar</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Apellido, nombre, email o DNI">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1">Rol</label>
                <select name="rol" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($roles as $valor => $texto)
                        <option value="{{ $valor }}" @selected(request('rol') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="activos" @selected(request('estado') === 'activos')>Activos</option>
                    <option value="inactivos" @selected(request('estado') === 'inactivos')>Inactivos</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-grow-1"><i class="bi bi-search"></i> Filtrar</button>
                @if (request()->hasAny(['q', 'rol', 'estado']))
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
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
                    <th>Usuario</th>
                    <th class="d-none d-md-table-cell">Email</th>
                    <th class="d-none d-sm-table-cell">DNI</th>
                    <th>Rol</th>
                    <th class="d-none d-lg-table-cell">Último acceso</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $u)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $u->apellido }}, {{ $u->nombre }}</div>
                            @if ($u->id === auth()->id())
                                <span class="badge text-bg-info">Vos</span>
                            @endif
                        </td>
                        <td class="d-none d-md-table-cell small">{{ $u->email ?: '—' }}</td>
                        <td class="d-none d-sm-table-cell small">{{ $u->dni ? number_format($u->dni, 0, ',', '.') : '—' }}</td>
                        <td><span class="badge text-bg-light border">{{ $u->rol->label() }}</span></td>
                        <td class="d-none d-lg-table-cell small text-muted">
                            {{ $u->ultimo_acceso_at?->format('d/m/Y H:i') ?? 'Nunca' }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $u->activo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $u->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('usuarios.edit', $u) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @unless ($u->id === auth()->id())
                                <form method="POST" action="{{ route('usuarios.estado', $u) }}" class="d-inline"
                                      data-confirmar="{{ $u->activo ? '¿Deshabilitar a ' . $u->nombre_completo . '? No va a poder ingresar.' : '¿Habilitar a ' . $u->nombre_completo . '?' }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $u->activo ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            title="{{ $u->activo ? 'Deshabilitar' : 'Habilitar' }}">
                                        <i class="bi {{ $u->activo ? 'bi-person-slash' : 'bi-person-check' }}"></i>
                                    </button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No hay usuarios para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($usuarios->hasPages())
        <div class="card-footer bg-white">{{ $usuarios->links() }}</div>
    @endif
</div>

@endsection
