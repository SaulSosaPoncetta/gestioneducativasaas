@extends('layouts.app')

@section('titulo', 'Cursadas de ' . $estudiante->nombre_completo)

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Cursadas · {{ $estudiante->nombre_completo }}</h1>
        <p class="text-muted mb-0">{{ $plan->nombre }} · {{ $ciclo->nombre }}</p>
    </div>
    <div class="d-flex gap-2">
        @if ($carreras->count() > 1)
            <form method="GET">
                <select name="plan" class="form-select" onchange="this.form.submit()">
                    @foreach ($carreras as $c)
                        <option value="{{ $c->id }}" @selected($plan->id === $c->id)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </form>
        @endif
        <a href="{{ route('analitico.show', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-ruled me-1"></i> Analítico</a>
        <a href="{{ route('estudiantes.show', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Legajo</a>
    </div>
</div>

<form method="POST" action="{{ route('cursadas.guardar', $estudiante) }}">
    @csrf
    <input type="hidden" name="plan" value="{{ $plan->id }}">
    <input type="hidden" name="ciclo" value="{{ $ciclo->id }}">

    @foreach ($espacios->groupBy(fn ($f) => $f->espacio->grado) as $anio => $lista)
        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $anio }}° año</span>
                <button type="button" class="btn btn-sm btn-link marcar-anio" data-anio="{{ $anio }}">Marcar las disponibles</button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                        @foreach ($lista as $f)
                            @php
                                $estado = $f->estado->estado;
                                [$texto, $color] = \App\Services\TrayectoriaService::estadoLabel($estado);
                                $disponible = in_array($estado, ['pendiente', 'libre'], true) && empty($f->faltan);
                                $bloqueada = in_array($estado, ['aprobada', 'cursando', 'regular'], true);
                            @endphp
                            <tr class="{{ $bloqueada ? 'text-muted' : '' }}">
                                <td style="width: 40px">
                                    @unless ($bloqueada)
                                        <input class="form-check-input espacio" type="checkbox" name="espacios[]" value="{{ $f->espacio->id }}"
                                               data-anio="{{ $anio }}" data-disponible="{{ $disponible ? 1 : 0 }}"
                                               @checked(in_array($f->espacio->id, old('espacios', [])))>
                                    @endunless
                                </td>
                                <td>
                                    {{ $f->espacio->nombre }}
                                    <span class="small text-muted">· {{ $f->espacio->regimen_label }}</span>
                                    @if ($f->faltan && ! $bloqueada)
                                        <div class="small text-danger"><i class="bi bi-lock"></i> Correlativas: le falta {{ implode(', ', $f->faltan) }}</div>
                                    @endif
                                </td>
                                <td class="text-end" style="width: 220px">
                                    <span class="badge text-bg-{{ $color }} {{ $color === 'light' ? 'border' : '' }}">{{ $texto }}</span>
                                    @if ($f->estado->nota) <span class="small">({{ rtrim(rtrim(number_format($f->estado->nota, 2, ',', ''), '0'), ',') }})</span> @endif
                                    @if ($estado === 'regular' && $f->estado->vence) <div class="small text-muted">vence {{ $f->estado->vence->format('d/m/Y') }}</div> @endif
                                </td>
                                <td style="width: 140px">
                                    @unless ($bloqueada)
                                        <input type="text" name="comision[{{ $f->espacio->id }}]" maxlength="30" value="Única"
                                               class="form-control form-control-sm" title="Comisión">
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="forzar" value="1" id="forzar">
            <label class="form-check-label small" for="forzar">Inscribir aunque no cumpla correlativas (excepción autorizada)</label>
        </div>
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Inscribir a las seleccionadas</button>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.marcar-anio').forEach((b) => b.addEventListener('click', () => {
            document.querySelectorAll(`.espacio[data-anio="${b.dataset.anio}"][data-disponible="1"]`).forEach((c) => (c.checked = true));
        }));
    });
</script>
@endpush

@endsection
