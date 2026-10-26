@extends('layouts.app')

@php
    $nivel     = $plan->nivel;
    $usaCampos = $nivel->nivel->usaCamposFormacion();
@endphp

@section('titulo', $espacio->exists ? 'Editar ' . $espacio->nombre : 'Nuevo espacio curricular')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $espacio->exists ? 'Editar ' . $espacio->nombre : 'Nuevo espacio curricular' }}</h1>
        <p class="text-muted mb-0">{{ $plan->nombre }} · {{ $nivel->nombre }}</p>
    </div>
    <a href="{{ route('planes.show', $plan) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<form method="POST" action="{{ $espacio->exists ? route('espacios.update', $espacio) : route('planes.espacios.store', $plan) }}">
    @csrf
    @if ($espacio->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="{{ $plan->esCarrera() ? 'col-xl-6' : 'col-lg-8' }}">
            <div class="card">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-journal-text me-1"></i> Datos</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" maxlength="150" value="{{ old('nombre', $espacio->nombre) }}"
                                   class="form-control @error('nombre') is-invalid @enderror" required autofocus>
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" name="codigo" id="codigo" maxlength="20" value="{{ old('codigo', $espacio->codigo) }}"
                                   class="form-control @error('codigo') is-invalid @enderror">
                            @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="grado" class="form-label">{{ $nivel->nivel->unidadCurso() }} <span class="text-danger">*</span></label>
                            <select name="grado" id="grado" class="form-select @error('grado') is-invalid @enderror">
                                @foreach ($plan->grados() as $g)
                                    <option value="{{ $g }}" @selected((int) old('grado', $espacio->grado) === $g)>{{ $nivel->nivel->etiquetaGrado($g) }}</option>
                                @endforeach
                            </select>
                            @error('grado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if ($plan->esCarrera())
                            <div class="col-md-4">
                                <label for="regimen" class="form-label">Régimen <span class="text-danger">*</span></label>
                                <select name="regimen" id="regimen" class="form-select @error('regimen') is-invalid @enderror">
                                    @foreach (\App\Models\EspacioCurricular::REGIMENES as $valor => $texto)
                                        <option value="{{ $valor }}" @selected(old('regimen', $espacio->regimen) === $valor)>{{ $texto }}</option>
                                    @endforeach
                                </select>
                                @error('regimen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @else
                            <input type="hidden" name="regimen" id="regimen" value="anual">
                        @endif

                        <div class="col-md-4">
                            <label for="formato" class="form-label">Formato <span class="text-danger">*</span></label>
                            <select name="formato" id="formato" class="form-select @error('formato') is-invalid @enderror">
                                @foreach (\App\Models\EspacioCurricular::FORMATOS as $valor => $texto)
                                    <option value="{{ $valor }}" @selected(old('formato', $espacio->formato) === $valor)>{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('formato') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if ($usaCampos)
                            <div class="col-md-8">
                                <label for="campo" class="form-label">Campo de formación</label>
                                <select name="campo" id="campo" class="form-select @error('campo') is-invalid @enderror">
                                    <option value="">Sin especificar</option>
                                    @foreach (\App\Models\EspacioCurricular::CAMPOS as $valor => $texto)
                                        <option value="{{ $valor }}" @selected(old('campo', $espacio->campo) === $valor)>{{ $texto }}</option>
                                    @endforeach
                                </select>
                                @error('campo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div class="col-6 col-md-4">
                            <label for="horas_semanales" class="form-label">Horas cátedra semanales</label>
                            <input type="number" name="horas_semanales" id="horas_semanales" min="1" max="40"
                                   value="{{ old('horas_semanales', $espacio->horas_semanales) }}"
                                   class="form-control @error('horas_semanales') is-invalid @enderror">
                            @error('horas_semanales') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="orden" class="form-label">Orden</label>
                            <input type="number" name="orden" id="orden" min="0" max="999" value="{{ old('orden', $espacio->orden) }}"
                                   class="form-control @error('orden') is-invalid @enderror">
                            <div class="form-text">Para ordenar dentro del año.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($plan->esCarrera())
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-diagram-2 me-1"></i> Correlatividades</div>
                    <div class="card-body">
                        @error('correlativas') <div class="alert alert-danger py-2 small">{{ $message }}</div> @enderror

                        @if ($candidatas->isEmpty())
                            <p class="small text-muted mb-0">Todavía no hay otros espacios en la carrera para elegir como correlativas.</p>
                        @else
                            <p class="small text-muted">Solo se pueden elegir espacios que se dictan antes que este.</p>
                            <p class="small text-muted d-none" id="sinCandidatas">Con el año y régimen elegidos no hay espacios anteriores.</p>

                            <ul class="nav nav-pills nav-fill small mb-3" role="tablist">
                                @foreach (\App\Models\EspacioCurricular::CORRELATIVIDADES as $tipo => $texto)
                                    <li class="nav-item">
                                        <button class="nav-link py-1 {{ $loop->first ? 'active' : '' }}" type="button"
                                                data-bs-toggle="pill" data-bs-target="#corr_{{ $tipo }}">
                                            {{ $texto }}
                                            <span class="badge text-bg-light border ms-1 contador" data-tipo="{{ $tipo }}">0</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content">
                                @foreach (\App\Models\EspacioCurricular::CORRELATIVIDADES as $tipo => $texto)
                                    @php $marcadas = collect(old("correlativas.$tipo", $elegidas->get($tipo, collect())->all()))->map(fn ($v) => (int) $v); @endphp
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="corr_{{ $tipo }}">
                                        <div class="vstack gap-1" style="max-height: 340px; overflow:auto">
                                            @foreach ($candidatas as $c)
                                                <label class="form-check candidata border rounded px-2 py-1 ps-4 mb-0"
                                                       data-grado="{{ $c->grado }}" data-regimen="{{ $c->regimen }}">
                                                    <input class="form-check-input" type="checkbox" name="correlativas[{{ $tipo }}][]"
                                                           value="{{ $c->id }}" data-tipo="{{ $tipo }}" @checked($marcadas->contains($c->id))>
                                                    <span class="form-check-label">
                                                        {{ $c->nombre }}
                                                        <span class="text-muted small">· {{ $nivel->nivel->etiquetaGrado($c->grado) }}{{ $c->regimen !== 'anual' ? ', ' . $c->regimen_label : '' }}</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
        <a href="{{ route('planes.show', $plan) }}" class="btn btn-outline-secondary">Cancelar</a>
        @unless ($espacio->exists)
            <button class="btn btn-outline-primary" name="otro" value="1"><i class="bi bi-plus-lg me-1"></i> Guardar y agregar otro</button>
        @endunless
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
    </div>
</form>

@if ($plan->esCarrera())
    @push('scripts')
    <script>
        // Muestra solo las correlativas que se dictan antes del año/régimen elegido.
        document.addEventListener('DOMContentLoaded', () => {
            const grado = document.getElementById('grado');
            const regimen = document.getElementById('regimen');

            const esAnterior = (el) => {
                const g = +el.dataset.grado, gActual = +grado.value;
                return g < gActual || (g === gActual && el.dataset.regimen === 'primer_cuatrimestre' && regimen.value === 'segundo_cuatrimestre');
            };

            const actualizar = () => {
                let visibles = 0;
                document.querySelectorAll('.candidata').forEach((el) => {
                    const ok = esAnterior(el);
                    el.classList.toggle('d-none', !ok);
                    const input = el.querySelector('input');
                    input.disabled = !ok;
                    if (!ok) input.checked = false;
                    if (ok) visibles++;
                });
                document.getElementById('sinCandidatas')?.classList.toggle('d-none', visibles > 0);
                document.querySelectorAll('.contador').forEach((b) => {
                    b.textContent = document.querySelectorAll(`input[data-tipo="${b.dataset.tipo}"]:checked`).length;
                });
            };

            grado.addEventListener('change', actualizar);
            regimen.addEventListener('change', actualizar);
            document.querySelectorAll('.candidata input').forEach((i) => i.addEventListener('change', actualizar));
            actualizar();
        });
    </script>
    @endpush
@endif

@endsection
