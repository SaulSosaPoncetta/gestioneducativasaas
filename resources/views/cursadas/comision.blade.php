@extends('layouts.app')

@section('titulo', $espacio->nombre . ' · ' . $comision)

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $espacio->nombre }}</h1>
        <p class="text-muted mb-0">{{ $espacio->plan->nombre }} · {{ $espacio->grado }}° año · Comisión {{ $comision }} · {{ $ciclo->nombre }}</p>
    </div>
    <div class="d-flex gap-2">
        @if ($comisiones->count() > 1)
            <form method="GET">
                <input type="hidden" name="ciclo" value="{{ $ciclo->id }}">
                <select name="comision" class="form-select" onchange="this.form.submit()">
                    @foreach ($comisiones as $c)
                        <option value="{{ $c }}" @selected($c === $comision)>Comisión {{ $c }}</option>
                    @endforeach
                </select>
            </form>
        @endif
        <a href="{{ route('cursadas.index', ['plan' => $espacio->plan_estudio_id, 'ciclo' => $ciclo->id]) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Cursadas</a>
    </div>
</div>

@if ($filas->isEmpty())
    <div class="alert alert-info">No hay estudiantes inscriptos en esta comisión.</div>
@else
<form method="POST" action="{{ route('cursadas.cerrar', $espacio) }}">
    @csrf @method('PUT')
    <input type="hidden" name="ciclo" value="{{ $ciclo->id }}">
    <div class="card">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-semibold">Cierre de cursada</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="aplicarSugerida"><i class="bi bi-magic me-1"></i> Aplicar condición sugerida</button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Estudiante</th>
                        <th class="text-center" title="Asistencia a la materia (GestiónAula)">Asistencia</th>
                        <th class="text-center" title="Promedio de las notas de los períodos (GestiónAula)">Promedio</th>
                        <th class="text-center">Sugerida</th>
                        <th style="width: 170px">Condición</th>
                        <th style="width: 100px">Nota</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($filas as $f)
                        <tr>
                            <td>
                                <a href="{{ route('estudiantes.show', $f->c->estudiante) }}" class="text-body text-decoration-none">{{ $f->c->estudiante->nombre_completo }}</a>
                                @if ($f->c->vence) <div class="small text-muted">Regular hasta {{ $f->c->vence->format('d/m/Y') }}</div> @endif
                            </td>
                            <td class="text-center {{ $f->porcentaje !== null && $f->porcentaje < \App\Models\Cursada::ASISTENCIA_MINIMA ? 'text-danger fw-semibold' : '' }}">
                                {{ $f->porcentaje !== null ? number_format($f->porcentaje, 0) . ' %' : '—' }}
                                @if ($f->clases) <div class="small text-muted">{{ $f->clases }} clases</div> @endif
                            </td>
                            <td class="text-center">{{ $f->promedio !== null ? number_format($f->promedio, 1, ',', '') : '—' }}</td>
                            <td class="text-center">
                                @if ($f->sugerida)
                                    <span class="badge text-bg-{{ \App\Models\Cursada::color($f->sugerida) }}">{{ \App\Models\Cursada::CONDICIONES[$f->sugerida] }}</span>
                                @else
                                    <span class="small text-muted">Sin datos</span>
                                @endif
                            </td>
                            <td>
                                <select name="cursadas[{{ $f->c->id }}][condicion]" class="form-select form-select-sm condicion"
                                        data-sugerida="{{ $f->sugerida }}" data-promedio="{{ $f->promedio }}">
                                    @foreach (\App\Models\Cursada::CONDICIONES as $v => $t)
                                        <option value="{{ $v }}" @selected($f->c->condicion === $v)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="cursadas[{{ $f->c->id }}][nota]" inputmode="decimal" maxlength="5"
                                       value="{{ $f->c->nota !== null ? rtrim(rtrim(number_format($f->c->nota, 2, ',', ''), '0'), ',') : '' }}"
                                       class="form-control form-control-sm text-center nota">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="small text-muted">
                Regular: puede rendir el final hasta {{ $ciclo->fecha_fin->copy()->addYears(\App\Models\Cursada::VIGENCIA_ANIOS)->format('d/m/Y') }}.
                Promocionado: queda aprobada con la nota indicada.
            </span>
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar condiciones</button>
        </div>
    </div>
</form>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('aplicarSugerida')?.addEventListener('click', () => {
            document.querySelectorAll('.condicion').forEach((s) => {
                if (!s.dataset.sugerida) return;
                s.value = s.dataset.sugerida;
                const nota = s.closest('tr').querySelector('.nota');
                if (!nota.value && s.dataset.promedio) nota.value = String(Math.round(+s.dataset.promedio)).replace('.', ',');
            });
        });
    });
</script>
@endpush

@endsection
