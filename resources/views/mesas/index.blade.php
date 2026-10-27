@extends('layouts.app')

@section('titulo', 'Mesas de examen')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Mesas de examen</h1>
        <p class="text-muted mb-0">Llamados a examen final, inscriptos y actas.</p>
    </div>
    <a href="{{ route('mesas.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nueva mesa</a>
</div>

<form method="GET" class="card mb-3">
    <div class="card-body row g-2">
        <div class="col-md-5">
            <select name="plan" class="form-select" onchange="this.form.submit()">
                <option value="">Todas las carreras</option>
                @foreach ($planes as $p)
                    <option value="{{ $p->id }}" @selected((int) request('plan') === $p->id)>{{ $p->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <div class="btn-group w-100">
                @foreach (['proximas' => 'Abiertas', 'cerradas' => 'Con acta cerrada', 'todas' => 'Todas'] as $v => $t)
                    <a href="{{ route('mesas.index', array_filter(['plan' => request('plan'), 'estado' => $v])) }}"
                       class="btn btn-sm {{ $estado === $v ? 'btn-primary' : 'btn-outline-primary' }}">{{ $t }}</a>
                @endforeach
            </div>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Unidad curricular</th>
                    <th class="d-none d-md-table-cell">Llamado</th>
                    <th class="d-none d-lg-table-cell">Inscripción</th>
                    <th class="text-center">Inscriptos</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mesas as $m)
                    <tr role="button" onclick="window.location='{{ route('mesas.show', $m) }}'">
                        <td class="text-nowrap">{{ $m->fecha->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('mesas.show', $m) }}" class="fw-semibold text-body text-decoration-none">{{ $m->espacio->nombre }}</a>
                            <div class="small text-muted">{{ $m->espacio->plan->nombre }} · {{ $m->espacio->grado }}° año</div>
                        </td>
                        <td class="d-none d-md-table-cell small">{{ $m->llamado }}</td>
                        <td class="d-none d-lg-table-cell small">
                            {{ $m->inscripcion_desde->format('d/m') }} al {{ $m->inscripcion_hasta->format('d/m') }}
                            @if ($m->inscripcionAbierta()) <span class="badge text-bg-success">Abierta</span> @endif
                        </td>
                        <td class="text-center">{{ $m->inscripciones_count }}</td>
                        <td class="text-center">
                            <span class="badge {{ $m->estaCerrada() ? 'text-bg-secondary' : 'text-bg-primary' }}">{{ $m->estaCerrada() ? 'Acta cerrada' : 'Abierta' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay mesas para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($mesas->hasPages())
        <div class="card-footer bg-white">{{ $mesas->links() }}</div>
    @endif
</div>

@endsection
