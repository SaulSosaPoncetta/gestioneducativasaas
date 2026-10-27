@extends('layouts.app')

@section('titulo', 'Nota de ' . $estudiante->nombre_completo)

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $espacio->nombre }} · {{ $periodo->nombre }}</h1>
        <p class="text-muted mb-0">{{ $estudiante->nombre_completo }} · {{ $periodo->ciclo->nombre }}</p>
    </div>
    <a href="{{ $volver }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<div class="row">
    <div class="col-lg-7">
        @if ($calificacion)
            <div class="alert {{ $calificacion->esManual() ? 'alert-info' : 'alert-secondary' }} small">
                @if ($calificacion->esManual())
                    <i class="bi bi-pencil me-1"></i> Corregida en secretaría por {{ $calificacion->modificador?->nombre ?? '—' }} el {{ $calificacion->updated_at->format('d/m/Y H:i') }}.
                    GestiónAula no la modifica mientras exista esta corrección.
                @else
                    <i class="bi bi-cloud-arrow-down me-1"></i> Enviada desde GestiónAula
                    @if ($calificacion->docente) por {{ $calificacion->docente->nombre_completo }} @endif
                    el {{ $calificacion->updated_at->format('d/m/Y H:i') }}. Si la corregís acá, queda fija.
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('calificaciones.guardar') }}" class="card">
            @csrf
            <input type="hidden" name="estudiante" value="{{ $estudiante->id }}">
            <input type="hidden" name="espacio" value="{{ $espacio->id }}">
            <input type="hidden" name="periodo" value="{{ $periodo->id }}">
            <input type="hidden" name="volver" value="{{ $volver }}">
            <div class="card-body vstack gap-3">
                @if ($escala === \App\Enums\EscalaCalificacion::Informe)
                    <div>
                        <label for="informe" class="form-label">Informe <span class="text-danger">*</span></label>
                        <textarea name="informe" id="informe" rows="8" maxlength="5000" class="form-control @error('informe') is-invalid @enderror" required>{{ old('informe', $calificacion?->informe) }}</textarea>
                        @error('informe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @elseif ($escala === \App\Enums\EscalaCalificacion::Conceptual)
                    <div>
                        <label class="form-label">Calificación <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach (\App\Enums\EscalaCalificacion::CONCEPTOS as $sigla => $nombre)
                                <input type="radio" class="btn-check" name="nota" id="n{{ $sigla }}" value="{{ $sigla }}" @checked(old('nota', $calificacion?->valor) === $sigla)>
                                <label class="btn btn-outline-primary" for="n{{ $sigla }}">{{ $nombre }}</label>
                            @endforeach
                        </div>
                        @error('nota') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                @else
                    <div style="max-width: 200px">
                        <label for="nota" class="form-label">Nota (1 a 10) <span class="text-danger">*</span></label>
                        <input type="text" name="nota" id="nota" inputmode="decimal" maxlength="5" value="{{ old('nota', $calificacion?->valor) }}"
                               class="form-control form-control-lg text-center @error('nota') is-invalid @enderror" required autofocus>
                        @error('nota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endif
                <div>
                    <label for="observacion" class="form-label">Observación</label>
                    <input type="text" name="observacion" id="observacion" maxlength="255" value="{{ old('observacion', $calificacion?->observacion) }}" class="form-control">
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between">
                @if ($calificacion)
                    <button type="submit" form="formEliminarNota" class="btn btn-link text-danger px-0"><i class="bi bi-trash me-1"></i> Eliminar nota</button>
                @else
                    <span></span>
                @endif
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
            </div>
        </form>

        @if ($calificacion)
            <form method="POST" action="{{ route('calificaciones.eliminar', $calificacion) }}" id="formEliminarNota"
                  data-confirmar="¿Eliminar la nota? GestiónAula podrá volver a enviarla.">
                @csrf @method('DELETE')
                <input type="hidden" name="volver" value="{{ $volver }}">
            </form>
        @endif
    </div>
</div>

@endsection
