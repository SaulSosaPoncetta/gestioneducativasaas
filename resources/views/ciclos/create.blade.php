@extends('layouts.app')

@section('titulo', 'Nuevo ciclo lectivo')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Nuevo ciclo lectivo</h1>
    <a href="{{ route('ciclos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<form method="POST" action="{{ route('ciclos.store') }}">
    @csrf

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-calendar3 me-1"></i> Datos del ciclo</div>
                <div class="card-body">
                    @include('ciclos.partials-campos')
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-layers me-1"></i> Evaluación por nivel</div>
                <div class="card-body">
                    <p class="small text-muted">
                        Cada nivel divide el año a su manera. Los períodos se generan con fechas
                        repartidas en partes iguales y después se ajustan desde el ciclo.
                    </p>

                    @error('niveles') <div class="alert alert-danger py-2 small">{{ $message }}</div> @enderror

                    <div class="vstack gap-3">
                        @foreach ($niveles as $n)
                            @php
                                $previos = $cursosAnteriores[$n->id] ?? 0;
                                $esquema = old("niveles.{$n->id}.esquema", $n->nivel->esquemaSugerido()->value);
                            @endphp
                            <div class="border rounded p-3">
                                <div class="fw-semibold mb-2"><i class="bi {{ $n->nivel->icono() }} me-1 text-primary"></i> {{ $n->nombre }}</div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-6">
                                        <select name="niveles[{{ $n->id }}][esquema]" class="form-select form-select-sm @error("niveles.{$n->id}.esquema") is-invalid @enderror">
                                            @foreach ($esquemas as $valor => $texto)
                                                <option value="{{ $valor }}" @selected($esquema === $valor)>{{ $texto }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 vstack gap-1">
                                        @if ($n->nivel->tieneIntensificacion())
                                            <div class="form-check mb-0">
                                                <input type="hidden" name="niveles[{{ $n->id }}][intensificacion]" value="0">
                                                <input class="form-check-input" type="checkbox" value="1" id="int_{{ $n->id }}"
                                                       name="niveles[{{ $n->id }}][intensificacion]"
                                                       @checked(old("niveles.{$n->id}.intensificacion", true))>
                                                <label class="form-check-label small" for="int_{{ $n->id }}">Intensificación (diciembre y febrero)</label>
                                            </div>
                                        @endif
                                        @if ($previos)
                                            <div class="form-check mb-0">
                                                <input type="hidden" name="niveles[{{ $n->id }}][copiar_cursos]" value="0">
                                                <input class="form-check-input" type="checkbox" value="1" id="cop_{{ $n->id }}"
                                                       name="niveles[{{ $n->id }}][copiar_cursos]"
                                                       @checked(old("niveles.{$n->id}.copiar_cursos", true))>
                                                <label class="form-check-label small" for="cop_{{ $n->id }}">
                                                    Copiar los {{ $previos }} cursos de {{ $anterior->anio }}
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('ciclos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Crear ciclo lectivo</button>
    </div>
</form>

@endsection
