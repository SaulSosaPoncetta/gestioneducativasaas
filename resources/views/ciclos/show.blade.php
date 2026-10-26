@extends('layouts.app')

@section('titulo', $ciclo->nombre)

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h1 class="h3 mb-0">{{ $ciclo->nombre }}</h1>
            <span class="badge text-bg-{{ $ciclo->estado_color }}">{{ $ciclo->estado_label }}</span>
        </div>
        <p class="text-muted mb-0">
            <i class="bi bi-calendar-range"></i>
            Clases del {{ $ciclo->fecha_inicio->format('d/m/Y') }} al {{ $ciclo->fecha_fin->format('d/m/Y') }}
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('ciclos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <a href="{{ route('ciclos.edit', $ciclo) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Editar</a>

        @if ($ciclo->estado !== \App\Models\CicloLectivo::EN_CURSO)
            <form method="POST" action="{{ route('ciclos.estado', $ciclo) }}"
                  data-confirmar="¿Poner en curso el {{ $ciclo->nombre }}? Si hay otro ciclo en curso, se va a cerrar.">
                @csrf @method('PATCH')
                <input type="hidden" name="estado" value="en_curso">
                <button class="btn btn-success"><i class="bi bi-play-fill me-1"></i> Poner en curso</button>
            </form>
        @endif

        @if ($ciclo->estado === \App\Models\CicloLectivo::EN_CURSO)
            <form method="POST" action="{{ route('ciclos.estado', $ciclo) }}"
                  data-confirmar="¿Cerrar el {{ $ciclo->nombre }}? Sus cursos quedan de solo lectura.">
                @csrf @method('PATCH')
                <input type="hidden" name="estado" value="cerrado">
                <button class="btn btn-outline-danger"><i class="bi bi-lock me-1"></i> Cerrar ciclo</button>
            </form>
        @endif

        @if ($ciclo->estado === \App\Models\CicloLectivo::PLANIFICACION)
            <form method="POST" action="{{ route('ciclos.destroy', $ciclo) }}"
                  data-confirmar="¿Eliminar el {{ $ciclo->nombre }} con sus períodos y cursos?">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
            </form>
        @endif
    </div>
</div>

<div class="vstack gap-3">
    @foreach ($niveles as $n)
        @php
            $lista   = $periodos->get($n->id, collect());
            $esquema = $servicio->esquemaDe($lista);
        @endphp

        <div class="card">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="fw-semibold">
                    <i class="bi {{ $n->nivel->icono() }} text-primary me-1"></i> {{ $n->nombre }}
                    @unless ($n->activo) <span class="badge text-bg-secondary ms-1">Nivel inactivo</span> @endunless
                </div>
                <div class="d-flex align-items-center gap-2 small">
                    @if ($n->nivel->usaCursos())
                        <a href="{{ route('cursos.index', ['ciclo' => $ciclo->id, 'nivel' => $n->id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-grid-3x3-gap me-1"></i> {{ $cursos[$n->id] ?? 0 }} cursos
                        </a>
                    @endif
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#esquema_{{ $n->id }}">
                        <i class="bi bi-arrow-repeat me-1"></i> {{ $esquema?->label() ?? 'Esquema personalizado' }}
                    </button>
                </div>
            </div>

            {{-- Regenerar períodos con otro esquema --}}
            <div class="collapse" id="esquema_{{ $n->id }}">
                <form method="POST" action="{{ route('ciclos.esquema', [$ciclo, $n]) }}"
                      class="border-bottom bg-light p-3 row g-2 align-items-center"
                      data-confirmar="Se van a reemplazar los períodos de {{ $n->nivel->label() }} con fechas nuevas. ¿Continuar?">
                    @csrf
                    <div class="col-md-5">
                        <select name="esquema" class="form-select form-select-sm">
                            @foreach ($esquemas as $valor => $texto)
                                <option value="{{ $valor }}" @selected(($esquema ?? $n->nivel->esquemaSugerido())->value === $valor)>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        @if ($n->nivel->tieneIntensificacion())
                            <div class="form-check mb-0">
                                <input type="hidden" name="intensificacion" value="0">
                                <input class="form-check-input" type="checkbox" name="intensificacion" value="1" id="int_{{ $n->id }}"
                                       @checked($lista->contains(fn ($p) => $p->esIntensificacion()))>
                                <label class="form-check-label small" for="int_{{ $n->id }}">Con intensificación</label>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3 text-md-end">
                        <button class="btn btn-sm btn-warning"><i class="bi bi-arrow-repeat me-1"></i> Regenerar períodos</button>
                    </div>
                </form>
            </div>

            @if ($lista->isEmpty())
                <div class="card-body text-muted small">
                    Este nivel no tiene períodos en el ciclo. Usá el botón de esquema para generarlos.
                </div>
            @else
                <form method="POST" action="{{ route('ciclos.periodos', $ciclo) }}">
                    @csrf @method('PUT')
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Período</th>
                                    <th style="width: 170px">Desde</th>
                                    <th style="width: 170px">Hasta</th>
                                    <th class="text-center" style="width: 110px">Cerrado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lista as $p)
                                    <tr @class(['table-warning' => $p->esIntensificacion()])>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="text" name="periodos[{{ $p->id }}][nombre]" maxlength="60"
                                                       value="{{ old("periodos.{$p->id}.nombre", $p->nombre) }}"
                                                       class="form-control form-control-sm">
                                                @if ($p->estaVigente())
                                                    <span class="badge text-bg-primary">Vigente</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <input type="date" name="periodos[{{ $p->id }}][fecha_inicio]"
                                                   value="{{ old("periodos.{$p->id}.fecha_inicio", $p->fecha_inicio->format('Y-m-d')) }}"
                                                   class="form-control form-control-sm">
                                        </td>
                                        <td>
                                            <input type="date" name="periodos[{{ $p->id }}][fecha_fin]"
                                                   value="{{ old("periodos.{$p->id}.fecha_fin", $p->fecha_fin->format('Y-m-d')) }}"
                                                   class="form-control form-control-sm">
                                        </td>
                                        <td class="text-center">
                                            <input type="hidden" name="periodos[{{ $p->id }}][cerrado]" value="0">
                                            <div class="form-check form-switch d-inline-block mb-0">
                                                <input class="form-check-input" type="checkbox" role="switch" value="1"
                                                       name="periodos[{{ $p->id }}][cerrado]" title="Cerrado: no se cargan más notas"
                                                       @checked(old("periodos.{$p->id}.cerrado", $p->cerrado))>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white text-end">
                        <button class="btn btn-sm btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar períodos</button>
                    </div>
                </form>
            @endif
        </div>
    @endforeach
</div>

@endsection
