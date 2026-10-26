@extends('layouts.app')

@section('titulo', 'Tutores y familias')

@section('contenido')

@php $puedeEditar = auth()->user()->rol->esGestion(); @endphp

<div class="mb-3">
    <h1 class="h3 mb-0">Tutores y familias</h1>
    <p class="text-muted mb-0">Responsables de los estudiantes. Se agregan desde el legajo de cada estudiante.</p>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-6">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Apellido, nombre o DNI">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary flex-grow-1"><i class="bi bi-search"></i> Buscar</button>
                @if (request('q'))
                    <a href="{{ route('tutores.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
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
                    <th>Tutor/a</th>
                    <th class="d-none d-md-table-cell">Contacto</th>
                    <th>Estudiantes a cargo</th>
                    @if ($puedeEditar) <th class="text-end">Acciones</th> @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($tutores as $t)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $t->nombre_completo }}</div>
                            <div class="small text-muted">DNI {{ number_format((int) $t->documento, 0, ',', '.') }}</div>
                        </td>
                        <td class="d-none d-md-table-cell small">
                            @if ($t->telefono) <div><i class="bi bi-telephone"></i> {{ $t->telefono }}</div> @endif
                            @if ($t->email) <div><i class="bi bi-envelope"></i> {{ $t->email }}</div> @endif
                        </td>
                        <td class="small">
                            @forelse ($t->estudiantes as $e)
                                <div>
                                    <a href="{{ route('estudiantes.show', $e) }}">{{ $e->nombre_completo }}</a>
                                    <span class="text-muted">· {{ \App\Models\Tutor::vinculoLabel($e->pivot->vinculo) }}</span>
                                </div>
                            @empty
                                <span class="text-muted">Sin estudiantes vinculados</span>
                            @endforelse
                        </td>
                        @if ($puedeEditar)
                            <td class="text-end text-nowrap">
                                <a href="{{ route('tutores.edit', $t) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                @if ($t->estudiantes->isEmpty())
                                    <form method="POST" action="{{ route('tutores.destroy', $t) }}" class="d-inline" data-confirmar="¿Eliminar a {{ $t->nombre_completo }}?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No hay tutores para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($tutores->hasPages())
        <div class="card-footer bg-white">{{ $tutores->links() }}</div>
    @endif
</div>

@endsection
