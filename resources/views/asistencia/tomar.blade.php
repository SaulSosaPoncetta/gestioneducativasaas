@extends('layouts.app')

@section('titulo', 'Asistencia ' . $curso->nombre)

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $curso->nombre }}</h1>
        <p class="text-muted mb-0">
            {{ ucfirst($fecha->translatedFormat('l j \d\e F')) }} · {{ $curso->turno_label }}
            @if ($toma) · <span class="text-success">Tomada por {{ $toma->usuario?->nombre ?? '—' }} {{ $toma->updated_at->format('H:i') }}</span> @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('asistencia.tomar', ['curso' => $curso, 'fecha' => $fecha->copy()->subDay()->toDateString()]) }}" class="btn btn-outline-secondary" title="Día anterior"><i class="bi bi-chevron-left"></i></a>
        @unless ($fecha->isToday())
            <a href="{{ route('asistencia.tomar', ['curso' => $curso, 'fecha' => $fecha->copy()->addDay()->toDateString()]) }}" class="btn btn-outline-secondary" title="Día siguiente"><i class="bi bi-chevron-right"></i></a>
        @endunless
        <a href="{{ route('asistencia.index', ['fecha' => $fecha->toDateString()]) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Cursos</a>
    </div>
</div>

{{-- Semana --}}
<div class="d-flex flex-wrap gap-1 mb-3">
    @foreach (\Carbon\CarbonPeriod::create($fecha->copy()->startOfWeek(), $fecha->copy()->startOfWeek()->addDays(4)) as $dia)
        @php $tomado = in_array($dia->toDateString(), $semana, true); @endphp
        <a href="{{ $dia->isFuture() ? '#' : route('asistencia.tomar', ['curso' => $curso, 'fecha' => $dia->toDateString()]) }}"
           class="btn btn-sm {{ $dia->isSameDay($fecha) ? 'btn-primary' : ($tomado ? 'btn-outline-success' : 'btn-outline-secondary') }} {{ $dia->isFuture() ? 'disabled' : '' }}">
            {{ ucfirst($dia->translatedFormat('D j')) }} @if ($tomado) <i class="bi bi-check"></i> @endif
        </a>
    @endforeach
</div>

@if ($estudiantes->isEmpty())
    <div class="alert alert-info">El curso no tenía estudiantes inscriptos en esa fecha.</div>
@else
<form method="POST" action="{{ route('asistencia.guardar', $curso) }}">
    @csrf
    <input type="hidden" name="fecha" value="{{ $fecha->toDateString() }}">

    <div class="card">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="small" id="resumen"></div>
            <button type="button" class="btn btn-sm btn-outline-success" id="todosPresentes"><i class="bi bi-check2-all me-1"></i> Todos presentes</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px" class="text-center">#</th>
                        <th>Estudiante</th>
                        <th style="width: 230px">Asistencia</th>
                        <th>Justificación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estudiantes as $n => $e)
                        @php
                            $a = $actual->get($e->id);
                            $estado = old("asistencia.{$e->id}.estado", $a?->estado ?? 'presente');
                        @endphp
                        <tr class="fila" data-estado="{{ $estado }}">
                            <td class="text-center text-muted">{{ $n + 1 }}</td>
                            <td>
                                <a href="{{ route('estudiantes.show', $e) }}" class="text-body text-decoration-none">{{ $e->nombre_completo }}</a>
                                @if (filled($e->salud_observaciones))
                                    <i class="bi bi-exclamation-triangle-fill text-warning ms-1" data-bs-toggle="tooltip" title="{{ $e->salud_observaciones }}"></i>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    @foreach (\App\Models\Asistencia::SIGLAS as $valor => $sigla)
                                        <input type="radio" class="btn-check estado" name="asistencia[{{ $e->id }}][estado]"
                                               id="e{{ $e->id }}{{ $sigla }}" value="{{ $valor }}" @checked($estado === $valor)>
                                        <label class="btn btn-outline-{{ \App\Models\Asistencia::color($valor) }}" for="e{{ $e->id }}{{ $sigla }}"
                                               title="{{ \App\Models\Asistencia::ESTADOS[$valor] }}">{{ $sigla }}</label>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="justif d-flex gap-2 align-items-center {{ $estado === 'presente' ? 'invisible' : '' }}">
                                    <div class="form-check mb-0">
                                        <input type="hidden" name="asistencia[{{ $e->id }}][justificada]" value="0">
                                        <input class="form-check-input" type="checkbox" name="asistencia[{{ $e->id }}][justificada]" value="1"
                                               id="j{{ $e->id }}" @checked(old("asistencia.{$e->id}.justificada", $a?->justificada))>
                                        <label class="form-check-label small" for="j{{ $e->id }}">Justif.</label>
                                    </div>
                                    <input type="text" name="asistencia[{{ $e->id }}][motivo]" maxlength="200" class="form-control form-control-sm"
                                           value="{{ old("asistencia.{$e->id}.motivo", $a?->motivo) }}" placeholder="Motivo">
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center sticky-bottom">
            <span class="small text-muted">P presente · A ausente (1) · T tarde (½) · R retiro anticipado (½)</span>
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar asistencia</button>
        </div>
    </div>
</form>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const resumen = document.getElementById('resumen');
        const actualizar = () => {
            const c = { presente: 0, ausente: 0, tarde: 0, retiro: 0 };
            document.querySelectorAll('.fila').forEach((f) => {
                const v = f.querySelector('.estado:checked')?.value || 'presente';
                c[v]++;
                f.querySelector('.justif').classList.toggle('invisible', v === 'presente');
            });
            if (resumen) resumen.innerHTML =
                `<span class="text-success">${c.presente} ${c.presente === 1 ? 'presente' : 'presentes'}</span> · ` +
                `<span class="text-danger">${c.ausente} ${c.ausente === 1 ? 'ausente' : 'ausentes'}</span> · ` +
                `<span class="text-warning-emphasis">${c.tarde} tarde · ${c.retiro} ${c.retiro === 1 ? 'retiro' : 'retiros'}</span>`;
        };
        document.querySelectorAll('.estado').forEach((r) => r.addEventListener('change', actualizar));
        document.getElementById('todosPresentes')?.addEventListener('click', () => {
            document.querySelectorAll('.estado[value="presente"]').forEach((r) => (r.checked = true));
            actualizar();
        });
        actualizar();
    });
</script>
@endpush

@endsection
