@extends('layouts.app')

@section('titulo', 'Estudiantes')

@section('contenido')

@php $puedeEditar = auth()->user()->rol->esGestion(); @endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Estudiantes</h1>
        <p class="text-muted mb-0">
            Legajos e inscripciones
            @if ($ciclo) · {{ $ciclo->nombre }} @endif
        </p>
    </div>
    @if ($puedeEditar)
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-key me-1"></i> Accesos a portales</button>
                <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 290px">
                    <form method="POST" action="{{ route('accesos.masivo') }}" data-confirmar="¿Generar el acceso de todas las familias de estudiantes activos que todavía no lo tienen?">
                        @csrf <input type="hidden" name="tipo" value="familias">
                        <button class="dropdown-item rounded">Generar accesos de familias</button>
                    </form>
                    @if (($institucionActual ?? null)?->nivelesActivos()->contains(fn ($n) => $n->usaCarreras()))
                        <form method="POST" action="{{ route('accesos.masivo') }}" data-confirmar="¿Generar el acceso de los estudiantes de nivel superior que todavía no lo tienen?">
                            @csrf <input type="hidden" name="tipo" value="superior">
                            <button class="dropdown-item rounded">Generar accesos de estudiantes (superior)</button>
                        </form>
                    @endif
                    <div class="small text-muted px-2 pt-1">Ingresan con su DNI; la contraseña inicial es el DNI.</div>
                </div>
            </div>
            <a href="{{ route('estudiantes.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Nuevo estudiante</a>
        </div>
    @endif
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label small text-muted mb-1">Buscar</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Apellido, nombre, DNI o legajo">
            </div>
            @if ($ciclos->isNotEmpty())
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Ciclo</label>
                    <select name="ciclo" class="form-select" onchange="this.form.curso && (this.form.curso.value = ''); this.form.submit()">
                        @foreach ($ciclos as $c)
                            <option value="{{ $c->id }}" @selected($ciclo?->id === $c->id)>{{ $c->anio }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Nivel</label>
                <select name="nivel" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($niveles as $n)
                        <option value="{{ $n->id }}" @selected((int) request('nivel') === $n->id)>{{ $n->nivel->labelCorto() }}</option>
                    @endforeach
                </select>
            </div>
            @if ($cursos->isNotEmpty())
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Curso</label>
                    <select name="curso" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($cursos->groupBy('institucion_nivel_id') as $lista)
                            <optgroup label="{{ $lista->first()->nivel->nivel->labelCorto() }}">
                                @foreach ($lista as $c)
                                    <option value="{{ $c->id }}" @selected((int) request('curso') === $c->id)>{{ $c->nombre_corto }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-6 col-md-2 col-lg-1">
                <label class="form-label small text-muted mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    @foreach (\App\Models\Estudiante::ESTADOS as $valor => $texto)
                        <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 col-lg-2 d-flex gap-2 align-items-center">
                <button class="btn btn-outline-primary flex-grow-1"><i class="bi bi-search"></i> Filtrar</button>
                @if (request()->hasAny(['q', 'nivel', 'curso', 'estado', 'sin_inscripcion']))
                    <a href="{{ route('estudiantes.index', ['ciclo' => $ciclo?->id]) }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
            <div class="col-12">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="sin_inscripcion" id="sin_inscripcion" value="1" @checked(request('sin_inscripcion'))>
                    <label class="form-check-label small" for="sin_inscripcion">Solo activos sin inscripción en el ciclo</label>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="d-none d-md-table-cell" style="width: 90px">Legajo</th>
                    <th>Estudiante</th>
                    <th class="d-none d-sm-table-cell">Documento</th>
                    <th class="d-none d-lg-table-cell text-center">Edad</th>
                    <th>Inscripción {{ $ciclo?->anio }}</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($estudiantes as $e)
                    <tr role="button" onclick="window.location='{{ route('estudiantes.show', $e) }}'">
                        <td class="d-none d-md-table-cell text-muted">{{ $e->legajo }}</td>
                        <td>
                            <a href="{{ route('estudiantes.show', $e) }}" class="fw-semibold text-decoration-none text-body">{{ $e->nombre_completo }}</a>
                        </td>
                        <td class="d-none d-sm-table-cell small">{{ $e->documento_label }}</td>
                        <td class="d-none d-lg-table-cell text-center">{{ $e->edad ?? '—' }}</td>
                        <td class="small">
                            @forelse ($e->inscripciones as $i)
                                <div>
                                    <i class="bi {{ $i->curso?->nivel->nivel->icono() ?? 'bi-mortarboard' }} text-primary"></i>
                                    {{ $i->destino }}
                                </div>
                            @empty
                                <span class="text-muted">Sin inscripción</span>
                            @endforelse
                        </td>
                        <td class="text-center"><span class="badge text-bg-{{ $e->estado_color }}">{{ $e->estado_label }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay estudiantes para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        @if ($estudiantes->hasPages())
            {{ $estudiantes->links() }}
        @else
            <span class="small text-muted">{{ $estudiantes->total() }} {{ $estudiantes->total() === 1 ? 'estudiante' : 'estudiantes' }}</span>
        @endif
    </div>
</div>

@endsection
