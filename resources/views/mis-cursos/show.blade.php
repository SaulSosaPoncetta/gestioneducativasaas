@extends('layouts.app')

@section('titulo', $asignacion->espacio->nombre . ' · ' . $asignacion->grupo)

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $asignacion->espacio->nombre }}</h1>
        <p class="text-muted mb-0">
            {{ $asignacion->grupo }} · {{ $asignacion->espacio->plan->nombre }} · {{ $asignacion->ciclo->nombre }}
            @if ($asignacion->curso?->preceptor) · Preceptor/a: {{ $asignacion->curso->preceptor->nombre_completo }} @endif
        </p>
    </div>
    <a href="{{ route('docente.cursos') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Mis cursos</a>
</div>

@if ($otros->count() > 1)
    <div class="mb-3 d-flex flex-wrap gap-2">
        @foreach ($otros as $o)
            <a href="{{ route('docente.cursos.show', $o) }}"
               class="btn btn-sm {{ $o->id === $asignacion->id ? 'btn-primary' : 'btn-outline-primary' }}">{{ $o->espacio->nombre }}</a>
        @endforeach
    </div>
@endif

<p class="small text-muted">
    <i class="bi bi-info-circle"></i>
    La asistencia y las notas de la materia se cargan en GestiónAula y se ven acá ({{ $clases }} {{ $clases === 1 ? 'clase recibida' : 'clases recibidas' }}).
</p>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-people me-1"></i> Estudiantes</span>
            <span class="small text-muted">{{ $inscripciones->count() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px" class="text-center">#</th>
                        <th>Apellido y nombre</th>
                        <th class="d-none d-sm-table-cell">Documento</th>
                        <th class="d-none d-md-table-cell text-center">Edad</th>
                        <th class="d-none d-md-table-cell">Condición</th>
                        <th class="text-center" title="Asistencia a la materia registrada en GestiónAula">Asist. materia</th>
                        @foreach ($periodos as $p)
                            <th class="text-center d-none d-lg-table-cell small" title="{{ $p->nombre }}">{{ \Illuminate\Support\Str::limit($p->nombre, 12) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inscripciones as $n => $i)
                        <tr>
                            <td class="text-center text-muted">{{ $n + 1 }}</td>
                            <td>
                                {{ $i->estudiante->nombre_completo }}
                                @if (filled($i->estudiante->salud_observaciones))
                                    <i class="bi bi-exclamation-triangle-fill text-warning ms-1" data-bs-toggle="tooltip"
                                       title="{{ $i->estudiante->salud_observaciones }}"></i>
                                @endif
                            </td>
                            <td class="d-none d-sm-table-cell small">{{ $i->estudiante->documento_label }}</td>
                            <td class="d-none d-md-table-cell text-center">{{ $i->estudiante->edad ?? '—' }}</td>
                            <td class="d-none d-md-table-cell small">
                                {{ $i->condicion_label }}
                                @if ($i->condicion === 'repitente') <i class="bi bi-arrow-repeat text-muted"></i> @endif
                            </td>
                            <td class="text-center">
                                @php $as = $asistencia->get($i->estudiante_id); @endphp
                                @if ($as)
                                    <span class="{{ $as->porcentaje < 75 ? 'text-danger fw-semibold' : '' }}"
                                          title="{{ $as->clases }} clases · {{ rtrim(rtrim(number_format($as->faltas, 1, ',', ''), '0'), ',') }} faltas">
                                        {{ number_format($as->porcentaje, 0) }} %
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            @foreach ($periodos as $p)
                                @php $nota = $notas->get("{$i->estudiante_id}-{$p->id}"); $ap = $nota ? $nivel->aprueba($nota->valor, $nota->valor_numerico) : null; @endphp
                                <td class="text-center d-none d-lg-table-cell {{ $ap === false ? 'text-danger fw-semibold' : '' }}">
                                    @if (! $nota) <span class="text-muted">·</span>
                                    @elseif ($nota->informe) <i class="bi bi-file-text" title="Informe cargado"></i>
                                    @else {{ $nota->valor }} @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ 6 + $periodos->count() }}" class="text-center text-muted py-4">{{ $asignacion->curso_id ? 'El curso no tiene estudiantes inscriptos.' : 'No hay estudiantes inscriptos a la cursada de esta comisión.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
