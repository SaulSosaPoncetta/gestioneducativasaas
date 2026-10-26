@extends('layouts.app')

@section('titulo', $plan->nombre)

@section('contenido')

@php
    $usaCampos = $nivel->nivel->usaCamposFormacion();
    $tiposCorr = \App\Models\EspacioCurricular::CORRELATIVIDADES;
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h1 class="h3 mb-0">{{ $plan->nombre }}</h1>
            @unless ($plan->activo) <span class="badge text-bg-secondary">Inactivo</span> @endunless
        </div>
        <p class="text-muted mb-0">
            {{ $nivel->nombre }} · {{ $plan->tipo_label }} · {{ $plan->alcance }}
            @if ($plan->modalidad) · {{ \App\Models\PlanEstudio::MODALIDADES[$plan->modalidad] ?? $plan->modalidad }} @endif
        </p>
        @if ($plan->titulo || $plan->resolucion)
            <p class="small text-muted mb-0">
                @if ($plan->titulo) <i class="bi bi-mortarboard"></i> {{ $plan->titulo }} @endif
                @if ($plan->resolucion) · Res. {{ $plan->resolucion }} @endif
            </p>
        @endif
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('planes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <a href="{{ route('planes.edit', $plan) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Editar</a>
        <form method="POST" action="{{ route('planes.duplicar', $plan) }}"
              data-confirmar="¿Duplicar el plan con todos sus espacios? Sirve para cargar una nueva versión.">
            @csrf
            <button class="btn btn-outline-secondary" title="Duplicar"><i class="bi bi-copy me-1"></i> Duplicar</button>
        </form>
        <form method="POST" action="{{ route('planes.destroy', $plan) }}"
              data-confirmar="¿Eliminar {{ $plan->nombre }} con todos sus espacios curriculares?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
        </form>
    </div>
</div>

<div class="vstack gap-3">
    @foreach ($plan->grados() as $grado)
        @php $espacios = $porAnio->get($grado, collect()); @endphp

        <div class="card">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="fw-semibold">{{ $nivel->nivel->etiquetaGrado($grado) }}</span>
                <div class="d-flex align-items-center gap-3">
                    @if ($espacios->sum('horas_semanales'))
                        <span class="small text-muted"><i class="bi bi-clock"></i> {{ $espacios->sum('horas_semanales') }} hs cátedra semanales</span>
                    @endif
                    <a href="{{ route('planes.espacios.create', ['plan' => $plan, 'grado' => $grado]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </a>
                </div>
            </div>

            @if ($espacios->isEmpty())
                <div class="card-body small text-muted">Sin {{ Str::lower($nivel->nivel->nombreEspacios()) }} cargadas.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ in_array($nivel->nivel->value, ['inicial', 'primario'], true) ? 'Área' : 'Espacio curricular' }}</th>
                                <th class="d-none d-md-table-cell">Formato</th>
                                @if ($plan->esCarrera()) <th class="d-none d-md-table-cell">Régimen</th> @endif
                                @if ($usaCampos) <th class="d-none d-lg-table-cell">Campo</th> @endif
                                <th class="text-center">Hs</th>
                                @if ($plan->esCarrera()) <th class="d-none d-lg-table-cell">Correlativas</th> @endif
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($espacios as $e)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $e->nombre }}</div>
                                        @if ($e->codigo) <div class="small text-muted">{{ $e->codigo }}</div> @endif
                                    </td>
                                    <td class="d-none d-md-table-cell small">{{ $e->formato_label }}</td>
                                    @if ($plan->esCarrera()) <td class="d-none d-md-table-cell small">{{ $e->regimen_label }}</td> @endif
                                    @if ($usaCampos) <td class="d-none d-lg-table-cell small">{{ $e->campo_label ?? '—' }}</td> @endif
                                    <td class="text-center">{{ $e->horas_semanales ?: '—' }}</td>
                                    @if ($plan->esCarrera())
                                        <td class="d-none d-lg-table-cell small">
                                            @forelse ($e->correlativas->groupBy('pivot.tipo') as $tipo => $lista)
                                                <div>
                                                    <span class="text-muted">{{ $tiposCorr[$tipo] ?? $tipo }}:</span>
                                                    {{ $lista->pluck('nombre')->join(', ') }}
                                                </div>
                                            @empty
                                                <span class="text-muted">—</span>
                                            @endforelse
                                        </td>
                                    @endif
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('espacios.edit', $e) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('espacios.destroy', $e) }}" class="d-inline"
                                              data-confirmar="¿Eliminar {{ $e->nombre }}? También se quitan sus correlatividades.">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach
</div>

@endsection
