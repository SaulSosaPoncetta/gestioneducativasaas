@extends('layouts.app')

@section('titulo', 'Constancia · ' . $estudiante->nombre_completo)

@php $tipo = old('tipo', $inscripcion ? 'alumno_regular' : ($examenes->isNotEmpty() ? 'examen' : 'alumno_regular')); @endphp

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <a href="{{ route('constancias.create') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Buscar otro estudiante</a>
        <h1 class="h3 mb-0">{{ $estudiante->nombre_completo }}</h1>
        <p class="text-muted mb-0">{{ $estudiante->documento_label }} · Legajo {{ $estudiante->legajo }}</p>
    </div>
    @if (Route::has('estudiantes.show'))
        <a href="{{ route('estudiantes.show', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-person-vcard me-1"></i> Legajo</a>
    @endif
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('constancias.store') }}" class="card">
            @csrf
            <input type="hidden" name="estudiante_id" value="{{ $estudiante->id }}">
            <div class="card-body">
                <h2 class="h6">Tipo de constancia</h2>

                <div class="form-check mb-2">
                    <input type="radio" name="tipo" value="alumno_regular" id="tipo_regular" class="form-check-input"
                           @checked($tipo === 'alumno_regular') @disabled(! $inscripcion)>
                    <label for="tipo_regular" class="form-check-label">
                        Alumno regular
                        @if ($inscripcion)
                            <span class="d-block small text-muted">{{ $inscripcion->destino }} · ciclo {{ $inscripcion->ciclo->anio }} · {{ $inscripcion->condicion_label }}</span>
                        @else
                            <span class="d-block small text-danger">Sin inscripción activa en el ciclo actual.</span>
                        @endif
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input type="radio" name="tipo" value="examen" id="tipo_examen" class="form-check-input"
                           @checked($tipo === 'examen') @disabled($examenes->isEmpty())>
                    <label for="tipo_examen" class="form-check-label">
                        Examen rendido
                        @if ($examenes->isEmpty())
                            <span class="d-block small text-muted">No tiene exámenes finales con acta cargada.</span>
                        @endif
                    </label>
                </div>
                @error('tipo') <div class="text-danger small">{{ $message }}</div> @enderror

                @if ($examenes->isNotEmpty())
                    <div id="opcionesExamen" class="ms-4 mt-2 {{ $tipo === 'examen' ? '' : 'd-none' }}">
                        <select name="mesa_inscripcion_id" class="form-select @error('mesa_inscripcion_id') is-invalid @enderror">
                            <option value="">Elegí el examen…</option>
                            @foreach ($examenes as $ex)
                                <option value="{{ $ex->id }}" @selected((int) old('mesa_inscripcion_id') === $ex->id)>
                                    {{ $ex->mesa->fecha->format('d/m/Y') }} · {{ $ex->mesa->espacio->nombre }} · {{ $ex->nota !== null ? rtrim(rtrim(number_format($ex->nota, 2, ',', ''), '0'), ',') : '' }} ({{ \App\Models\MesaInscripcion::RESULTADOS[$ex->resultado] }})
                                </option>
                            @endforeach
                        </select>
                        @error('mesa_inscripcion_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-check mt-2">
                            <input type="checkbox" name="incluir_nota" value="1" id="incluir_nota" class="form-check-input" @checked(old('incluir_nota', true))>
                            <label for="incluir_nota" class="form-check-label">Incluir la calificación</label>
                        </div>
                    </div>
                @endif

                <div class="mt-3">
                    <label class="form-label" for="presentar_ante">Para presentar ante <span class="text-muted small">(opcional)</span></label>
                    <input type="text" name="presentar_ante" id="presentar_ante" maxlength="150" value="{{ old('presentar_ante') }}"
                           class="form-control" placeholder="Ej.: ANSES, obra social, empleador. Vacío = quien corresponda">
                </div>
            </div>
            <div class="card-footer bg-white">
                <button class="btn btn-primary" @disabled(! $inscripcion && $examenes->isEmpty())>
                    <i class="bi bi-file-earmark-check me-1"></i> Emitir e imprimir
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white fw-semibold">Constancias anteriores</div>
            <ul class="list-group list-group-flush">
                @forelse ($anteriores as $c)
                    <li class="list-group-item d-flex justify-content-between align-items-center small">
                        <span>
                            <span class="fw-semibold">{{ $c->numero_label }}</span> · {{ $c->tipo_label }}
                            @if ($c->estaAnulada()) <span class="badge text-bg-danger">Anulada</span> @endif
                            <span class="d-block text-muted">{{ $c->created_at->format('d/m/Y') }}{{ $c->presentar_ante ? ' · ' . $c->presentar_ante : '' }}</span>
                        </span>
                        <a href="{{ route('constancias.imprimir', [$c, 'volver' => request()->fullUrl()]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer"></i></a>
                    </li>
                @empty
                    <li class="list-group-item text-muted small">Todavía no se le emitieron constancias.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const opciones = document.getElementById('opcionesExamen');
    document.querySelectorAll('input[name=tipo]').forEach(r => r.addEventListener('change', () => {
        opciones?.classList.toggle('d-none', r.value !== 'examen' || !r.checked);
    }));
});
</script>
@endpush
