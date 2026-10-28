@extends('layouts.app')

@section('titulo', 'Mesas de examen')

@section('contenido')

<div class="mb-3">
    <h1 class="h3 mb-0">Mesas de examen</h1>
    <p class="text-muted mb-0">Inscribite a los finales de tus carreras.</p>
</div>

@error('estudiante') <div class="alert alert-danger">{{ $message }}</div> @enderror
@error('mesa') <div class="alert alert-danger">{{ $message }}</div> @enderror

<div class="card mb-3">
    <div class="card-header bg-white fw-semibold">Próximas mesas</div>
    <div class="list-group list-group-flush">
        @forelse ($mesas as $m)
            <div class="list-group-item">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="fw-semibold">{{ $m->mesa->espacio->nombre }}</div>
                        <div class="small text-muted">
                            {{ ucfirst($m->mesa->fecha->translatedFormat('l j \d\e F, H:i')) }} hs
                            @if ($m->mesa->aula) · Aula {{ $m->mesa->aula }} @endif
                            · {{ $m->mesa->llamado }}
                        </div>
                        <div class="small">
                            Inscripción: {{ $m->mesa->inscripcion_desde->format('d/m') }} al {{ $m->mesa->inscripcion_hasta->format('d/m') }}
                            · Rendirías como <strong>{{ $m->inscripcion?->condicion ?? $m->condicion }}</strong>
                        </div>
                        @if (! $m->inscripcion && $m->motivo)
                            <div class="small text-danger"><i class="bi bi-info-circle"></i> {{ $m->motivo }}</div>
                        @endif
                    </div>
                    <div>
                        @if ($m->inscripcion)
                            <span class="badge text-bg-success me-2"><i class="bi bi-check-lg"></i> Inscripto</span>
                            @if ($m->mesa->inscripcionAbierta())
                                <form method="POST" action="{{ route('estudiante.finales.baja', $m->mesa) }}" class="d-inline"
                                      data-confirmar="¿Darte de baja de la mesa de {{ $m->mesa->espacio->nombre }}?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Darme de baja</button>
                                </form>
                            @endif
                        @elseif ($m->puede)
                            <form method="POST" action="{{ route('estudiante.finales.inscribir', $m->mesa) }}">
                                @csrf
                                <button class="btn btn-sm btn-primary"><i class="bi bi-pencil-square me-1"></i> Inscribirme</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="list-group-item text-muted small">No hay mesas abiertas para tus materias.</div>
        @endforelse
    </div>
</div>

<div class="card">
    <div class="card-header bg-white fw-semibold">Mis exámenes rendidos</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <tbody>
                @forelse ($historial as $h)
                    <tr>
                        <td>{{ $h->mesa->fecha->format('d/m/Y') }}</td>
                        <td>{{ $h->mesa->espacio->nombre }}</td>
                        <td class="text-center">{{ $h->nota !== null ? rtrim(rtrim(number_format($h->nota, 2, ',', ''), '0'), ',') : '' }}</td>
                        <td class="text-end"><span class="badge text-bg-{{ \App\Models\MesaInscripcion::color($h->resultado) }}">{{ \App\Models\MesaInscripcion::RESULTADOS[$h->resultado] }}</span></td>
                    </tr>
                @empty
                    <tr><td class="text-muted small">Todavía no rendiste exámenes finales.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
