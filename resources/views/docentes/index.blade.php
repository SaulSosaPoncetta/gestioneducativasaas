@extends('layouts.app')

@section('titulo', 'Docentes')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Docentes</h1>
        <p class="text-muted mb-0">Legajos docentes y sus asignaciones @if ($ciclo) del {{ $ciclo->nombre }} @endif</p>
    </div>
    <a href="{{ route('docentes.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Nuevo docente</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Buscar</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Apellido, nombre o DNI">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    @foreach (\App\Models\Docente::ESTADOS as $v => $t)
                        <option value="{{ $v }}" @selected(request('estado') === $v)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4 d-flex gap-2">
                <button class="btn btn-outline-primary flex-grow-1"><i class="bi bi-search"></i> Filtrar</button>
                @if (request()->hasAny(['q', 'estado', 'sin_asignacion']))
                    <a href="{{ route('docentes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
            <div class="col-12">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="sin_asignacion" id="sin_asignacion" value="1" @checked(request('sin_asignacion'))>
                    <label class="form-check-label small" for="sin_asignacion">Solo activos sin asignaciones en el ciclo</label>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Docente</th>
                    <th class="d-none d-md-table-cell">Contacto</th>
                    <th class="d-none d-lg-table-cell">Título</th>
                    <th class="text-center">Espacios {{ $ciclo?->anio }}</th>
                    <th class="text-center d-none d-sm-table-cell">Hs</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($docentes as $d)
                    <tr role="button" onclick="window.location='{{ route('docentes.show', $d) }}'">
                        <td>
                            <a href="{{ route('docentes.show', $d) }}" class="fw-semibold text-decoration-none text-body">{{ $d->nombre_completo }}</a>
                            <div class="small text-muted">
                                {{ $d->documento_label }}
                                @if ($d->usuario) · <i class="bi bi-key" title="Tiene usuario"></i> @endif
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell small">
                            @if ($d->telefono) <div><i class="bi bi-telephone"></i> {{ $d->telefono }}</div> @endif
                            @if ($d->email) <div><i class="bi bi-envelope"></i> {{ $d->email }}</div> @endif
                        </td>
                        <td class="d-none d-lg-table-cell small">{{ $d->titulo ?: '—' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $d->asignaciones_ciclo ? 'text-bg-primary' : 'text-bg-light border' }}">{{ $d->asignaciones_ciclo }}</span>
                        </td>
                        <td class="text-center d-none d-sm-table-cell">{{ $d->horas_ciclo ?: '—' }}</td>
                        <td class="text-center"><span class="badge text-bg-{{ $d->estado_color }}">{{ $d->estado_label }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay docentes para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        @if ($docentes->hasPages())
            {{ $docentes->links() }}
        @else
            <span class="small text-muted">{{ $docentes->total() }} {{ $docentes->total() === 1 ? 'docente' : 'docentes' }}</span>
        @endif
    </div>
</div>

@endsection
