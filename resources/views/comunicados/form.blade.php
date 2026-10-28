@extends('layouts.app')

@section('titulo', $comunicado->exists ? 'Editar comunicado' : 'Nuevo comunicado')

@php
    $alcance = old('alcance', $comunicado->alcance);
    $idsSel  = array_map('intval', (array) old('ids.' . $alcance, $comunicado->alcance_ids ?? []));
    $paraSel = old('para', $comunicado->para ?? []);
@endphp

@section('contenido')

<div class="mb-3">
    <a href="{{ $comunicado->exists ? route('comunicados.show', $comunicado) : route('comunicados.index') }}" class="small text-decoration-none">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
    <h1 class="h3 mb-0">{{ $comunicado->exists ? 'Editar comunicado' : 'Nuevo comunicado' }}</h1>
    <p class="text-muted mb-0">Se guarda como borrador; antes de publicar ves a cuántas personas llega.</p>
</div>

<form method="POST" enctype="multipart/form-data"
      action="{{ $comunicado->exists ? route('comunicados.update', $comunicado) : route('comunicados.store') }}">
    @csrf
    @if ($comunicado->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="titulo">Título</label>
                        <input type="text" name="titulo" id="titulo" maxlength="150" required
                               value="{{ old('titulo', $comunicado->titulo) }}"
                               class="form-control @error('titulo') is-invalid @enderror" placeholder="Ej.: Reunión de padres de 3° grado">
                        @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="cuerpo">Mensaje</label>
                        <textarea name="cuerpo" id="cuerpo" rows="10" required
                                  class="form-control @error('cuerpo') is-invalid @enderror">{{ old('cuerpo', $comunicado->cuerpo) }}</textarea>
                        <div class="form-text">Texto simple. Los links (https://...) se pueden clickear.</div>
                        @error('cuerpo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="adjunto">Adjunto <span class="text-muted small">(opcional, hasta 10 MB: PDF, imagen, Word, Excel)</span></label>
                        @if ($comunicado->adjunto_path)
                            <div class="d-flex align-items-center gap-2 mb-2 small">
                                <i class="bi bi-paperclip"></i> {{ $comunicado->adjunto_nombre }}
                                <div class="form-check ms-2">
                                    <input type="checkbox" name="quitar_adjunto" value="1" id="quitar_adjunto" class="form-check-input">
                                    <label for="quitar_adjunto" class="form-check-label">Quitar</label>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="adjunto" id="adjunto" class="form-control @error('adjunto') is-invalid @enderror"
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.odt,.ods">
                        @error('adjunto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check form-switch">
                        <input type="checkbox" name="requiere_confirmacion" value="1" id="requiere_confirmacion" class="form-check-input"
                               @checked(old('requiere_confirmacion', $comunicado->requiere_confirmacion))>
                        <label for="requiere_confirmacion" class="form-check-label">Pedir confirmación de lectura <span class="text-muted small">(tipo "notificado")</span></label>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="enviar_email" value="1" id="enviar_email" class="form-check-input"
                               @checked(old('enviar_email', $comunicado->enviar_email))>
                        <label for="enviar_email" class="form-check-label">Avisar también por email</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6">¿Para quiénes?</h2>
                    <div class="d-flex flex-wrap gap-3 mb-1">
                        @foreach (\App\Models\Comunicado::PARA as $v => $t)
                            <div class="form-check">
                                <input type="checkbox" name="para[]" value="{{ $v }}" id="para_{{ $v }}" class="form-check-input"
                                       @checked(in_array($v, $paraSel, true))>
                                <label for="para_{{ $v }}" class="form-check-label">{{ $t }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('para') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                    <p class="form-text mt-0">Familias: tutores que reciben avisos. Docentes: los que tienen materias en ese destino.</p>

                    <h2 class="h6 mt-3">¿Dónde?</h2>
                    @if (! $ciclo)
                        <div class="alert alert-warning small">No hay ciclo lectivo actual: no se pueden calcular destinatarios.</div>
                    @endif

                    <div class="d-flex flex-wrap gap-2 mb-3" role="group">
                        @foreach ($alcances as $v => $t)
                            <input type="radio" class="btn-check" name="alcance" value="{{ $v }}" id="alcance_{{ $v }}" @checked($alcance === $v)>
                            <label class="btn btn-sm btn-outline-primary" for="alcance_{{ $v }}">{{ $t }}</label>
                        @endforeach
                    </div>
                    @error('alcance_ids') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                    <div class="opciones-alcance" data-alcance="institucion">
                        <p class="text-muted small mb-0">Llega a todos los inscriptos del ciclo {{ $ciclo?->anio }} en todos los niveles.</p>
                    </div>

                    @if ($niveles->isNotEmpty())
                        <div class="opciones-alcance" data-alcance="niveles">
                            @foreach ($niveles as $n)
                                <div class="form-check">
                                    <input type="checkbox" name="ids[niveles][]" value="{{ $n->id }}" id="niv_{{ $n->id }}" class="form-check-input"
                                           @checked($alcance === 'niveles' && in_array($n->id, $idsSel, true))>
                                    <label for="niv_{{ $n->id }}" class="form-check-label">{{ $n->nombre }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="opciones-alcance" data-alcance="cursos">
                        @forelse ($cursos as $nivel => $lista)
                            <div class="mb-2">
                                <div class="small fw-semibold text-muted">{{ $nivel }}
                                    <a href="#" class="ms-1 fw-normal" data-marcar-todos>todos</a>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($lista as $c)
                                        <div class="form-check" style="min-width: 8rem">
                                            <input type="checkbox" name="ids[cursos][]" value="{{ $c->id }}" id="cur_{{ $c->id }}" class="form-check-input"
                                                   @checked($alcance === 'cursos' && in_array($c->id, $idsSel, true))>
                                            <label for="cur_{{ $c->id }}" class="form-check-label">{{ $c->nombre_corto }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No hay cursos disponibles en el ciclo actual.</p>
                        @endforelse
                    </div>

                    @if ($carreras->isNotEmpty())
                        <div class="opciones-alcance" data-alcance="carreras">
                            @foreach ($carreras as $p)
                                <div class="form-check">
                                    <input type="checkbox" name="ids[carreras][]" value="{{ $p->id }}" id="car_{{ $p->id }}" class="form-check-input"
                                           @checked($alcance === 'carreras' && in_array($p->id, $idsSel, true))>
                                    <label for="car_{{ $p->id }}" class="form-check-label">{{ $p->nombre }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Guardar borrador</button>
        <a href="{{ $comunicado->exists ? route('comunicados.show', $comunicado) : route('comunicados.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const mostrar = () => {
        const elegido = document.querySelector('input[name="alcance"]:checked')?.value;
        document.querySelectorAll('.opciones-alcance').forEach(el => el.classList.toggle('d-none', el.dataset.alcance !== elegido));
    };
    document.querySelectorAll('input[name="alcance"]').forEach(r => r.addEventListener('change', mostrar));
    mostrar();

    document.querySelectorAll('[data-marcar-todos]').forEach(a => a.addEventListener('click', e => {
        e.preventDefault();
        const cajas = a.closest('.mb-2').querySelectorAll('input[type=checkbox]');
        const todas = [...cajas].every(c => c.checked);
        cajas.forEach(c => c.checked = !todas);
    }));
});
</script>
@endpush
