@extends('layouts.app')

@section('titulo', 'Cursos y divisiones')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Cursos y divisiones</h1>
        <p class="text-muted mb-0">Salas, grados y años de cada ciclo lectivo.</p>
    </div>

    @if ($ciclos->isNotEmpty())
        <form method="GET" class="d-flex align-items-center gap-2">
            @if ($nivel) <input type="hidden" name="nivel" value="{{ $nivel->id }}"> @endif
            <label for="ciclo" class="small text-muted text-nowrap">Ciclo lectivo</label>
            <select name="ciclo" id="ciclo" class="form-select" onchange="this.form.submit()">
                @foreach ($ciclos as $c)
                    <option value="{{ $c->id }}" @selected($ciclo?->id === $c->id)>{{ $c->anio }} · {{ $c->estado_label }}</option>
                @endforeach
            </select>
        </form>
    @endif
</div>

@if (! $ciclo)
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar3 display-5 text-primary"></i>
            <p class="mt-3 mb-3">Para armar los cursos primero hay que crear el ciclo lectivo.</p>
            <a href="{{ route('ciclos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Crear ciclo lectivo</a>
        </div>
    </div>
@elseif ($niveles->isEmpty())
    <div class="alert alert-info">
        La institución no tiene niveles organizados por cursos (inicial, primaria o secundaria).
        Las carreras de nivel superior se gestionan desde "Carreras y planes".
    </div>
@else
    @if ($ciclo->estaCerrado())
        <div class="alert alert-secondary"><i class="bi bi-lock me-1"></i> El {{ $ciclo->nombre }} está cerrado: los cursos son de solo lectura.</div>
    @endif

    <ul class="nav nav-tabs mb-0">
        @foreach ($niveles as $n)
            <li class="nav-item">
                <a class="nav-link {{ $nivel->id === $n->id ? 'active' : '' }}"
                   href="{{ route('cursos.index', ['ciclo' => $ciclo->id, 'nivel' => $n->id]) }}">
                    <i class="bi {{ $n->nivel->icono() }} me-1"></i>{{ $n->nivel->labelCorto() }}
                    <span class="badge rounded-pill text-bg-light border ms-1">{{ $totales[$n->id] ?? 0 }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card rounded-top-0">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-semibold">{{ $nivel->nombre }} · {{ $ciclo->anio }}</span>
            @unless ($ciclo->estaCerrado())
                <a href="{{ route('cursos.create', ['ciclo' => $ciclo->id, 'nivel' => $nivel->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo curso
                </a>
            @endunless
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px">{{ $nivel->nivel->unidadCurso() }}</th>
                        <th>{{ $nivel->nivel->unidadDivision() }}</th>
                        <th class="d-none d-md-table-cell">Turno</th>
                        <th class="d-none d-lg-table-cell">Preceptor/a</th>
                        <th class="d-none d-lg-table-cell">Aula</th>
                        <th class="d-none d-xl-table-cell">Plan</th>
                        <th class="d-none d-sm-table-cell text-center">Inscriptos</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nivel->nivel->grados($nivel->duracion) as $grado)
                        @php $lista = $cursos->get($grado, collect()); @endphp

                        @forelse ($lista as $i => $curso)
                            <tr>
                                @if ($i === 0)
                                    <td rowspan="{{ $lista->count() }}" class="fw-semibold border-end">
                                        {{ $nivel->nivel->etiquetaGrado($grado) }}
                                        @unless ($ciclo->estaCerrado())
                                            <a href="{{ route('cursos.create', ['ciclo' => $ciclo->id, 'nivel' => $nivel->id, 'grado' => $grado]) }}"
                                               class="d-block small fw-normal mt-1"><i class="bi bi-plus"></i> Agregar</a>
                                        @endunless
                                    </td>
                                @endif
                                <td><span class="badge text-bg-primary fs-6 fw-semibold">{{ $curso->division }}</span></td>
                                <td class="d-none d-md-table-cell">{{ $curso->turno_label }}</td>
                                <td class="d-none d-lg-table-cell">{{ $curso->preceptor?->nombre_completo ?? '—' }}</td>
                                <td class="d-none d-lg-table-cell">{{ $curso->aula ?: '—' }}</td>
                                <td class="d-none d-xl-table-cell small">{{ $curso->plan?->nombre ?? '—' }}</td>
                                <td class="d-none d-sm-table-cell text-center">
                                    @php $inscriptos = $curso->inscripciones_activas_count; @endphp
                                    @if (Route::has('estudiantes.index'))
                                        <a href="{{ route('estudiantes.index', ['curso' => $curso->id]) }}"
                                           class="badge text-decoration-none {{ $curso->cupo && $inscriptos >= $curso->cupo ? 'text-bg-danger' : 'text-bg-light border' }}">
                                            {{ $inscriptos }}{{ $curso->cupo ? ' / ' . $curso->cupo : '' }}
                                        </a>
                                    @else
                                        {{ $inscriptos }}{{ $curso->cupo ? ' / ' . $curso->cupo : '' }}
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @unless ($ciclo->estaCerrado())
                                        <a href="{{ route('cursos.edit', $curso) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('cursos.destroy', $curso) }}" class="d-inline"
                                              data-confirmar="¿Eliminar {{ $curso->nombre }}?">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr class="text-muted">
                                <td class="fw-semibold border-end">{{ $nivel->nivel->etiquetaGrado($grado) }}</td>
                                <td colspan="6" class="small">Sin {{ Str::lower(Str::plural($nivel->nivel->unidadDivision())) }}</td>
                                <td class="text-end">
                                    @unless ($ciclo->estaCerrado())
                                        <a href="{{ route('cursos.create', ['ciclo' => $ciclo->id, 'nivel' => $nivel->id, 'grado' => $grado]) }}"
                                           class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg"></i> Agregar</a>
                                    @endunless
                                </td>
                            </tr>
                        @endforelse
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
