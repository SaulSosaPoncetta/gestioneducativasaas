@extends('layouts.app')

@section('titulo', 'Mesa ' . $mesa->espacio->nombre)

@section('contenido')

@php $num = fn ($v) => $v === null ? '' : rtrim(rtrim(number_format($v, 2, ',', ''), '0'), ','); @endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h1 class="h3 mb-0">{{ $mesa->espacio->nombre }}</h1>
            <span class="badge {{ $mesa->estaCerrada() ? 'text-bg-secondary' : 'text-bg-primary' }}">{{ $mesa->estaCerrada() ? 'Acta cerrada' : 'Abierta' }}</span>
        </div>
        <p class="text-muted mb-0">
            {{ $mesa->espacio->plan->nombre }} · {{ $mesa->llamado }} · {{ $mesa->fecha->format('d/m/Y H:i') }}
            @if ($mesa->aula) · Aula {{ $mesa->aula }} @endif
        </p>
        <p class="small mb-0">
            Tribunal: {{ $mesa->presidente?->nombre_completo ?? '—' }}@if ($mesa->vocales), {{ $mesa->vocales }}@endif
            · Inscripción {{ $mesa->inscripcion_desde->format('d/m') }} al {{ $mesa->inscripcion_hasta->format('d/m') }}
            @if ($mesa->inscripcionAbierta()) <span class="badge text-bg-success">abierta</span> @endif
            · {{ $mesa->admite_libres ? 'Admite libres' : 'Solo regulares' }}
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('mesas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <a href="{{ route('mesas.imprimir', $mesa) }}" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i> Acta</a>
        @unless ($mesa->estaCerrada())
            <a href="{{ route('mesas.edit', $mesa) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
            @if ($inscriptos->isEmpty())
                <form method="POST" action="{{ route('mesas.destroy', $mesa) }}" data-confirmar="¿Eliminar la mesa?">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            @endif
        @endunless
    </div>
</div>

@error('estudiante') <div class="alert alert-danger">{{ $message }}</div> @enderror
@error('acta') <div class="alert alert-danger">{{ $message }}</div> @enderror

<div class="row g-3">
    <div class="{{ $mesa->estaCerrada() ? 'col-12' : 'col-xl-8' }}">
        <form method="POST" action="{{ route('mesas.acta', $mesa) }}" class="card">
            @csrf @method('PUT')
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-journal-check me-1"></i> Acta · {{ $inscriptos->count() }} inscriptos</span>
                <span class="small text-muted">Aprueba con {{ $num($aprobacion) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Estudiante</th>
                            <th class="d-none d-sm-table-cell">Condición</th>
                            <th style="width: 110px">Nota</th>
                            <th class="text-center" style="width: 90px">Ausente</th>
                            <th class="text-center">Resultado</th>
                            @unless ($mesa->estaCerrada()) <th></th> @endunless
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inscriptos as $n => $i)
                            <tr>
                                <td class="text-muted">{{ $n + 1 }}</td>
                                <td>
                                    {{ $i->estudiante->nombre_completo }}
                                    <div class="small text-muted">{{ $i->estudiante->documento_label }}</div>
                                </td>
                                <td class="d-none d-sm-table-cell"><span class="badge text-bg-light border">{{ ucfirst($i->condicion) }}</span></td>
                                <td>
                                    <input type="text" name="acta[{{ $i->id }}][nota]" inputmode="decimal" maxlength="5"
                                           value="{{ $num($i->nota) }}" class="form-control form-control-sm text-center" @disabled($mesa->estaCerrada())>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input" type="checkbox" name="acta[{{ $i->id }}][ausente]" value="1"
                                           @checked($i->resultado === 'ausente') @disabled($mesa->estaCerrada())>
                                </td>
                                <td class="text-center">
                                    @if ($i->resultado)
                                        <span class="badge text-bg-{{ \App\Models\MesaInscripcion::color($i->resultado) }}">{{ \App\Models\MesaInscripcion::RESULTADOS[$i->resultado] }}</span>
                                    @else
                                        <span class="small text-muted">—</span>
                                    @endif
                                </td>
                                @unless ($mesa->estaCerrada())
                                    <td class="text-end">
                                        <button type="submit" form="quitar{{ $i->id }}" class="btn btn-sm btn-link text-danger" title="Quitar"><i class="bi bi-x-lg"></i></button>
                                    </td>
                                @endunless
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Todavía no hay inscriptos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (! $mesa->estaCerrada() && $inscriptos->isNotEmpty())
                <div class="card-footer bg-white text-end">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar notas</button>
                </div>
            @elseif ($mesa->estaCerrada())
                <div class="card-footer bg-white small text-muted">
                    Acta cerrada el {{ $mesa->cerrada_at?->format('d/m/Y H:i') }}
                    @if ($mesa->libro) · Libro {{ $mesa->libro }} @endif @if ($mesa->folio) · Folio {{ $mesa->folio }} @endif
                </div>
            @endif
        </form>

        @foreach ($inscriptos as $i)
            <form method="POST" action="{{ route('mesas.quitar', [$mesa, $i]) }}" id="quitar{{ $i->id }}" class="d-none"
                  data-confirmar="¿Quitar a {{ $i->estudiante->nombre_completo }} de la mesa?">
                @csrf @method('DELETE')
            </form>
        @endforeach
    </div>

    @unless ($mesa->estaCerrada())
        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-person-plus me-1"></i> Inscribir</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('mesas.inscribir', $mesa) }}" class="vstack gap-2">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="dni" class="form-control" placeholder="DNI del estudiante" inputmode="numeric" required>
                            <button class="btn btn-primary">Inscribir</button>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="forzar" value="1" id="forzar">
                            <label class="form-check-label small" for="forzar">Excepción: fuera de término o sin correlativas</label>
                        </div>
                    </form>
                    <p class="small text-muted mt-2 mb-0">Se verifica que no la tenga aprobada, su condición (regular o libre) y las correlativas para rendir.</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold small">Regulares que todavía no se inscribieron</div>
                <ul class="list-group list-group-flush">
                    @forelse ($candidatos as $c)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="small">{{ $c->nombre_completo }}</span>
                            <form method="POST" action="{{ route('mesas.inscribir', $mesa) }}">
                                @csrf
                                <input type="hidden" name="estudiante_id" value="{{ $c->id }}">
                                <button class="btn btn-sm btn-outline-primary py-0">Inscribir</button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item small text-muted">No hay.</li>
                    @endforelse
                </ul>
            </div>

            @if ($inscriptos->isNotEmpty())
                <form method="POST" action="{{ route('mesas.cerrar', $mesa) }}" class="card"
                      data-confirmar="¿Cerrar el acta? Las notas quedan fijas y no se puede inscribir a nadie más.">
                    @csrf
                    <div class="card-header bg-white fw-semibold small"><i class="bi bi-lock me-1"></i> Cerrar acta</div>
                    <div class="card-body row g-2">
                        <div class="col-6"><input type="text" name="libro" maxlength="20" class="form-control form-control-sm" placeholder="Libro"></div>
                        <div class="col-6"><input type="text" name="folio" maxlength="20" class="form-control form-control-sm" placeholder="Folio"></div>
                        <div class="col-12"><button class="btn btn-sm btn-outline-danger w-100">Cerrar acta</button></div>
                    </div>
                </form>
            @endif
        </div>
    @endunless
</div>

@endsection
