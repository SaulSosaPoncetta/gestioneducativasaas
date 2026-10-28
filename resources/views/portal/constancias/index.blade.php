@extends('layouts.app')

@section('titulo', 'Constancias')

@section('contenido')

<div class="mb-3">
    <h1 class="h3 mb-0">Constancias</h1>
    <p class="text-muted mb-0">
        Descargá {{ $esFamilia ? 'la constancia de tus hijos' : 'tu constancia' }} al instante. Tiene un código QR para que quien la reciba pueda verificarla.
    </p>
</div>

<div class="row g-3">
    @foreach ($estudiantes as $x)
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    @if ($esFamilia)
                        <h2 class="h5 mb-1">{{ $x->e->nombre }} {{ $x->e->apellido }}</h2>
                    @endif

                    @if ($x->inscripcion || $x->examenes->isNotEmpty())
                        <form method="POST" action="{{ route('portal.constancias.store') }}">
                            @csrf
                            <input type="hidden" name="estudiante_id" value="{{ $x->e->id }}">

                            <div class="form-check">
                                <input type="radio" name="tipo" value="alumno_regular" id="reg_{{ $x->e->id }}" class="form-check-input"
                                       @checked($x->inscripcion) @disabled(! $x->inscripcion)>
                                <label for="reg_{{ $x->e->id }}" class="form-check-label">
                                    Alumno regular
                                    <span class="d-block small text-muted">
                                        {{ $x->inscripcion ? $x->inscripcion->destino . ' · ciclo ' . $x->inscripcion->ciclo->anio : 'Sin inscripción activa este ciclo.' }}
                                    </span>
                                </label>
                            </div>

                            @if ($x->examenes->isNotEmpty())
                                <div class="form-check mt-2">
                                    <input type="radio" name="tipo" value="examen" id="ex_{{ $x->e->id }}" class="form-check-input" @checked(! $x->inscripcion)>
                                    <label for="ex_{{ $x->e->id }}" class="form-check-label">Examen rendido</label>
                                </div>
                                <select name="mesa_inscripcion_id" class="form-select form-select-sm mt-1 ms-4" style="max-width: calc(100% - 1.5rem)" aria-label="Examen">
                                    @foreach ($x->examenes as $ex)
                                        <option value="{{ $ex->id }}">{{ $ex->mesa->fecha->format('d/m/Y') }} · {{ $ex->mesa->espacio->nombre }}</option>
                                    @endforeach
                                </select>
                            @endif

                            <label class="form-label small mt-3 mb-1" for="ante_{{ $x->e->id }}">Para presentar ante (opcional)</label>
                            <input type="text" name="presentar_ante" id="ante_{{ $x->e->id }}" maxlength="150" class="form-control"
                                   placeholder="Ej.: ANSES, obra social, trabajo">

                            <button class="btn btn-primary mt-3"><i class="bi bi-file-earmark-arrow-down me-1"></i> Obtener constancia</button>
                        </form>
                    @else
                        <p class="text-muted mb-0">No hay constancias disponibles: no tiene inscripción activa en el ciclo actual. Consultá en secretaría.</p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

@if ($emitidas->isNotEmpty())
    <div class="card mt-3">
        <div class="card-header bg-white fw-semibold">Constancias emitidas</div>
        <ul class="list-group list-group-flush">
            @foreach ($emitidas as $c)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small">
                        <span class="fw-semibold">{{ $c->tipo_label }}</span> {{ $c->numero_label }}
                        @if ($esFamilia) · {{ $c->datos['estudiante']['nombre'] }} @endif
                        <span class="d-block text-muted">
                            {{ $c->created_at->format('d/m/Y') }}
                            @if ($c->tipo === 'examen') · {{ $c->datos['espacio'] }} @endif
                            · {{ $c->presentar_ante ?: 'Quien corresponda' }}
                        </span>
                    </span>
                    <a href="{{ route('portal.constancias.show', $c) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer"></i></a>
                </li>
            @endforeach
        </ul>
    </div>
@endif

@endsection
