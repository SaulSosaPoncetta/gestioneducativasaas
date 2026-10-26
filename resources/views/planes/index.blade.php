@extends('layouts.app')

@section('titulo', 'Planes de estudio')

@section('contenido')

<div class="mb-3">
    <h1 class="h3 mb-0">Planes de estudio</h1>
    <p class="text-muted mb-0">Planes, orientaciones, especialidades y carreras con sus espacios curriculares.</p>
</div>

<div class="vstack gap-3">
    @forelse ($niveles as $n)
        @php $lista = $planes->get($n->id, collect()); @endphp

        <div class="card">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="fw-semibold"><i class="bi {{ $n->nivel->icono() }} text-primary me-1"></i> {{ $n->nombre }}</span>
                <a href="{{ route('planes.create', ['nivel' => $n->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> {{ $n->nivel->usaCarreras() ? 'Nueva carrera' : 'Nuevo plan' }}
                </a>
            </div>

            @if ($lista->isEmpty())
                <div class="card-body text-muted small">
                    @if ($n->nivel->usaCarreras())
                        Todavía no hay carreras cargadas.
                    @elseif (count($n->nivel->tiposPlan()) > 1)
                        Todavía no hay planes. Cargá el ciclo básico y cada {{ Str::lower($n->nivel->nombreOrientacion()) }} del ciclo superior.
                    @else
                        Todavía no hay plan cargado para este nivel.
                    @endif
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach ($lista as $plan)
                        <a href="{{ route('planes.show', $plan) }}" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-semibold">
                                        {{ $plan->nombre }}
                                        @unless ($plan->activo) <span class="badge text-bg-secondary ms-1">Inactivo</span> @endunless
                                    </div>
                                    <div class="small text-muted">
                                        {{ $plan->tipo_label }} · {{ $plan->alcance }}
                                        @if ($plan->titulo) · {{ $plan->titulo }} @endif
                                        @if ($plan->resolucion) · Res. {{ $plan->resolucion }} @endif
                                    </div>
                                </div>
                                <div class="text-end small text-muted">
                                    <div><i class="bi bi-journal-text"></i> {{ $plan->espacios_count }} {{ Str::lower($n->nivel->nombreEspacios()) }}</div>
                                    @if ($plan->espacios_sum_horas_semanales)
                                        <div><i class="bi bi-clock"></i> {{ $plan->espacios_sum_horas_semanales }} hs cátedra semanales (sumando todos los años)</div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="alert alert-warning">La institución no tiene niveles activos.</div>
    @endforelse
</div>

@endsection
