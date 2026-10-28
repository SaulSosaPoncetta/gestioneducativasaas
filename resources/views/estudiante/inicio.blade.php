@extends('layouts.app')

@section('titulo', 'Mi cursada')

@push('estilos')
<style>@media print { body { background: #fff !important; } .card { box-shadow: none !important; border: 1px solid #999 !important; } }</style>
@endpush

@section('contenido')

@php $num = fn ($v) => $v === null ? '' : rtrim(rtrim(number_format((float) $v, 1, ',', ''), '0'), ','); @endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
    <div>
        <h1 class="h3 mb-0">¡Hola, {{ $estudiante->nombre }}!</h1>
        <p class="text-muted mb-0">{{ $ciclo?->nombre }}</p>
    </div>
    @if ($boletin)
        <button type="button" class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir boletín</button>
    @endif
</div>

@if ($carreras->isEmpty())
    {{-- Niveles escolares: boletín --}}
    @php $datos = $boletin; @endphp
    @include('calificaciones.partials.boletin')
@else
    {{-- Nivel superior: cursadas del ciclo --}}
    <div class="row g-3 mb-3 no-print">
        <div class="col-md-4">
            <a href="{{ route('estudiante.cursadas') }}" class="card h-100 text-decoration-none text-body">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="tarjeta-icono bg-primary-subtle text-primary"><i class="bi bi-journal-plus"></i></span>
                    <div><div class="fw-semibold">Inscripción a cursadas</div><div class="small text-muted">{{ $ciclo?->inscripcionCursadasAbierta() ? 'Abierta hasta el ' . $ciclo->inscripcion_cursadas_hasta->format('d/m') : 'Cerrada' }}</div></div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('estudiante.finales') }}" class="card h-100 text-decoration-none text-body">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="tarjeta-icono bg-success-subtle text-success"><i class="bi bi-pencil-square"></i></span>
                    <div><div class="fw-semibold">Mesas de examen</div><div class="small text-muted">Inscribirme a finales</div></div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('estudiante.analitico') }}" class="card h-100 text-decoration-none text-body">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="tarjeta-icono bg-warning-subtle text-warning-emphasis"><i class="bi bi-file-earmark-ruled"></i></span>
                    <div><div class="fw-semibold">Mi analítico</div><div class="small text-muted">Materias aprobadas y promedio</div></div>
                </div>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white fw-semibold">Mis cursadas · {{ $carreras->pluck('nombre')->join(', ') }}</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Unidad curricular</th><th class="text-center">Condición</th><th class="text-center">Asistencia</th><th>Notas</th></tr>
                </thead>
                <tbody>
                    @forelse ($cursadas as $f)
                        <tr>
                            <td>{{ $f->cursada->espacio->nombre }} <div class="small text-muted">{{ $f->cursada->espacio->grado }}° año · Comisión {{ $f->cursada->comision }}</div></td>
                            <td class="text-center">
                                <span class="badge text-bg-{{ \App\Models\Cursada::color($f->cursada->condicion) }}">{{ $f->cursada->condicion_label }}</span>
                                @if ($f->cursada->nota) <div class="small">{{ $num($f->cursada->nota) }}</div> @endif
                            </td>
                            <td class="text-center {{ $f->asistencia && $f->asistencia->porcentaje < \App\Models\Cursada::ASISTENCIA_MINIMA ? 'text-danger fw-semibold' : '' }}">
                                {{ $f->asistencia ? number_format($f->asistencia->porcentaje, 0) . ' %' : '—' }}
                            </td>
                            <td class="small">
                                @forelse ($f->notas as $n)
                                    <span class="me-2">{{ $n->periodo->nombre }}: <strong>{{ $n->valor }}</strong></span>
                                @empty
                                    <span class="text-muted">Sin notas</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No tenés cursadas en este ciclo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
