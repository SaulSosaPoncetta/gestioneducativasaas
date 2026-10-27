@extends('layouts.app')

@section('titulo', 'Planilla mensual ' . $curso->nombre)

@section('contenido')

@php
    $porFecha = $tomas->keyBy(fn ($t) => $t->fecha->toDateString());
    $celda = fn ($toma, $estudianteId) => $toma?->asistencias->firstWhere('estudiante_id', $estudianteId);
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $curso->nombre }} · {{ ucfirst($mes->translatedFormat('F Y')) }}</h1>
        <p class="text-muted mb-0">{{ $tomas->count() }} días con asistencia tomada en el mes</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('asistencia.mensual', ['curso' => $curso, 'mes' => $mes->copy()->subMonth()->format('Y-m')]) }}" class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
        <a href="{{ route('asistencia.mensual', ['curso' => $curso, 'mes' => $mes->copy()->addMonth()->format('Y-m')]) }}" class="btn btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i></button>
        <a href="{{ route('asistencia.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Cursos</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0 text-center small">
            <thead class="table-light">
                <tr>
                    <th class="text-start" style="min-width: 200px">Estudiante</th>
                    @foreach ($tomas as $t)
                        <th style="min-width: 30px">
                            <a href="{{ route('asistencia.tomar', ['curso' => $curso, 'fecha' => $t->fecha->toDateString()]) }}" class="text-decoration-none">{{ $t->fecha->day }}</a>
                        </th>
                    @endforeach
                    <th title="Inasistencias del ciclo">Inasist.</th>
                    <th title="Justificadas">Just.</th>
                    <th title="Asistencia del ciclo">%</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($estudiantes as $e)
                    @php $tot = $totales[$e->id]; @endphp
                    <tr>
                        <td class="text-start"><a href="{{ route('estudiantes.show', $e) }}" class="text-body text-decoration-none">{{ $e->nombre_completo }}</a></td>
                        @foreach ($tomas as $t)
                            @php $a = $celda($t, $e->id); @endphp
                            <td class="{{ $a && $a->estado !== 'presente' ? 'table-' . \App\Models\Asistencia::color($a->estado) : '' }}"
                                title="{{ $a ? $a->estado_label . ($a->justificada ? ' (justificada' . ($a->motivo ? ': ' . $a->motivo : '') . ')' : '') : '' }}">
                                @if ($a)
                                    {{ $a->estado === 'presente' ? '·' : \App\Models\Asistencia::SIGLAS[$a->estado] }}@if ($a->justificada)<sup>j</sup>@endif
                                @endif
                            </td>
                        @endforeach
                        <td class="fw-semibold">{{ rtrim(rtrim(number_format($tot['inasistencias'], 1, ',', ''), '0'), ',') }}</td>
                        <td>{{ rtrim(rtrim(number_format($tot['justificadas'], 1, ',', ''), '0'), ',') }}</td>
                        <td class="{{ $tot['porcentaje'] !== null && $tot['porcentaje'] < 85 ? 'text-danger fw-semibold' : '' }}">
                            {{ $tot['porcentaje'] !== null ? number_format($tot['porcentaje'], 1, ',', '') : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $tomas->count() + 4 }}" class="text-muted py-4">Sin estudiantes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-muted">
        · presente · A ausente · T tarde · R retiro anticipado · <sup>j</sup> justificada.
        Inasistencias y % son del ciclo completo. En rojo, asistencia menor al 85 %.
    </div>
</div>

@endsection
