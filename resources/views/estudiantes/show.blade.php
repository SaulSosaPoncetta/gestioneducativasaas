@extends('layouts.app')

@section('titulo', $estudiante->nombre_completo)

@section('contenido')

@php
    $puedeEditar = auth()->user()->rol->esGestion();
    $dato = fn ($valor) => filled($valor) ? e($valor) : '<span class="text-muted">—</span>';
    $activas = $estudiante->inscripciones->where('estado', 'activa');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div class="d-flex align-items-center gap-3">
        <span class="avatar" style="width:56px;height:56px;font-size:1.2rem">{{ mb_substr($estudiante->nombre, 0, 1) }}{{ mb_substr($estudiante->apellido, 0, 1) }}</span>
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h1 class="h3 mb-0">{{ $estudiante->nombre_completo }}</h1>
                <span class="badge text-bg-{{ $estudiante->estado_color }}">{{ $estudiante->estado_label }}</span>
            </div>
            <div class="text-muted">
                Legajo N° {{ $estudiante->legajo }} · {{ $estudiante->documento_label }}
                @if ($estudiante->edad !== null) · {{ $estudiante->edad }} años @endif
            </div>
            @foreach ($activas as $i)
                <span class="badge text-bg-primary mt-1"><i class="bi bi-mortarboard me-1"></i>{{ $i->destino }} · {{ $i->ciclo->anio }}</span>
            @endforeach
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('estudiantes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        @php $esSuperior = $estudiante->inscripciones->contains(fn ($i) => ! $i->curso_id && $i->plan_estudio_id); @endphp
        @if ($esSuperior)
            <a href="{{ route('analitico.show', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-ruled me-1"></i> Analítico</a>
            @if ($puedeEditar)
                <a href="{{ route('cursadas.inscribir', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-journal-bookmark me-1"></i> Cursadas</a>
            @endif
        @else
            <a href="{{ route('calificaciones.boletin', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-text me-1"></i> Boletín</a>
        @endif
        @if (Route::has('auditoria.index') && auth()->user()->tieneRol('admin'))
            <a href="{{ route('auditoria.index', ['modelo' => 'Estudiante', 'id' => $estudiante->id]) }}" class="btn btn-outline-secondary" title="Historial de cambios"><i class="bi bi-clock-history"></i></a>
        @endif
        @if (Route::has('constancias.create'))
            <a href="{{ route('constancias.create', ['estudiante' => $estudiante->id]) }}" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-check me-1"></i> Constancia</a>
        @endif
        @if ($puedeEditar)
            <a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Editar</a>
            <a href="{{ route('estudiantes.inscripciones.create', $estudiante) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i> Inscribir</a>
        @endif
    </div>
</div>

@if (filled($estudiante->salud_observaciones))
    <div class="alert alert-warning d-flex gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div><strong>Salud:</strong> {{ $estudiante->salud_observaciones }}</div>
    </div>
@endif

<div class="row g-3">
    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-vcard me-1"></i> Datos personales</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Fecha de nacimiento</dt>
                    <dd class="col-7">{!! $dato($estudiante->fecha_nacimiento?->format('d/m/Y')) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Género</dt>
                    <dd class="col-7">{!! $dato(\App\Models\Estudiante::GENEROS[$estudiante->genero] ?? null) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">CUIL</dt>
                    <dd class="col-7">{!! $dato($estudiante->cuil) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Nacionalidad</dt>
                    <dd class="col-7">{!! $dato($estudiante->nacionalidad) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Lugar de nacimiento</dt>
                    <dd class="col-7">{!! $dato($estudiante->lugar_nacimiento) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Domicilio</dt>
                    <dd class="col-7">{!! $dato(trim($estudiante->domicilio . ($estudiante->localidad ? ', ' . $estudiante->localidad : ''), ', ')) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Teléfono</dt>
                    <dd class="col-7">{!! $dato($estudiante->telefono) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Email</dt>
                    <dd class="col-7">{!! $dato($estudiante->email) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Ingreso</dt>
                    <dd class="col-7 mb-0">{!! $dato($estudiante->fecha_ingreso?->format('d/m/Y')) !!}</dd>
                </dl>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-heart-pulse me-1"></i> Salud y emergencias</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Grupo sanguíneo</dt>
                    <dd class="col-7">{!! $dato($estudiante->grupo_sanguineo) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Obra social</dt>
                    <dd class="col-7">{!! $dato(trim($estudiante->obra_social . ($estudiante->numero_afiliado ? ' · N° ' . $estudiante->numero_afiliado : ''))) !!}</dd>
                    <dt class="col-5 text-muted fw-normal">Emergencias</dt>
                    <dd class="col-7 mb-0">{!! $dato(trim($estudiante->emergencia_contacto . ($estudiante->emergencia_telefono ? ' · ' . $estudiante->emergencia_telefono : ''))) !!}</dd>
                </dl>
            </div>
        </div>

        @if (filled($estudiante->observaciones))
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-chat-left-text me-1"></i> Observaciones</div>
                <div class="card-body small">{{ $estudiante->observaciones }}</div>
            </div>
        @endif
    </div>

    <div class="col-xl-7">
        {{-- Familia --}}
        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-house-heart me-1"></i> Familia y responsables</span>
                @if ($puedeEditar)
                    <a href="{{ route('estudiantes.tutores.create', $estudiante) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg"></i> Agregar</a>
                @endif
            </div>
            @if ($estudiante->tutores->isEmpty())
                <div class="card-body small text-muted">No hay tutores cargados.</div>
            @else
                <div class="list-group list-group-flush">
                    @foreach ($estudiante->tutores as $t)
                        <div class="list-group-item">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div>
                                    <div class="fw-semibold">
                                        {{ $t->nombre_completo }}
                                        <span class="text-muted fw-normal">· {{ \App\Models\Tutor::vinculoLabel($t->pivot->vinculo) }}</span>
                                        @if ($t->pivot->es_responsable) <span class="badge text-bg-primary ms-1">Responsable</span> @endif
                                    </div>
                                    <div class="small text-muted">
                                        DNI {{ number_format((int) $t->documento, 0, ',', '.') }}
                                        @if ($t->telefono) · <i class="bi bi-telephone"></i> {{ $t->telefono }} @endif
                                        @if ($t->email) · <i class="bi bi-envelope"></i> {{ $t->email }} @endif
                                    </div>
                                    <div class="small mt-1 d-flex flex-wrap gap-2">
                                        <span class="{{ $t->pivot->autorizado_retirar ? 'text-success' : 'text-danger' }}">
                                            <i class="bi {{ $t->pivot->autorizado_retirar ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                            {{ $t->pivot->autorizado_retirar ? 'Autorizado a retirar' : 'NO autorizado a retirar' }}
                                        </span>
                                        @if ($t->pivot->convive) <span class="text-muted"><i class="bi bi-house"></i> Convive</span> @endif
                                        @if ($t->pivot->recibe_notificaciones) <span class="text-muted"><i class="bi bi-bell"></i> Recibe avisos</span> @endif
                                    </div>
                                </div>
                                @if ($puedeEditar)
                                    <div class="text-nowrap">
                                        @if ($t->user_id)
                                            <form method="POST" action="{{ route('accesos.restablecer', $t->user_id) }}" class="d-inline"
                                                  data-confirmar="¿Volver la contraseña de {{ $t->nombre_completo }} a su DNI?">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success" title="Tiene acceso al portal. Clic: restablecer contraseña"><i class="bi bi-key-fill"></i></button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('accesos.tutor', $t) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-secondary" title="Crear acceso al portal de familias"><i class="bi bi-key"></i></button>
                                            </form>
                                        @endif
                                        <a href="{{ route('estudiantes.tutores.edit', [$estudiante, $t]) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('estudiantes.tutores.destroy', [$estudiante, $t]) }}" class="d-inline"
                                              data-confirmar="¿Desvincular a {{ $t->nombre_completo }} de este estudiante?">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Desvincular"><i class="bi bi-link-45deg"></i></button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Acceso al portal del estudiante --}}
        @if ($puedeEditar)
            <div class="card mb-3">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                    <span class="small">
                        <i class="bi bi-person-badge me-1"></i> Portal del estudiante:
                        @if ($estudiante->user_id)
                            <span class="text-success">tiene acceso</span> (ingresa con su DNI)
                        @else
                            <span class="text-muted">sin acceso</span>
                        @endif
                    </span>
                    @if ($estudiante->user_id)
                        <form method="POST" action="{{ route('accesos.restablecer', $estudiante->user_id) }}" data-confirmar="¿Volver la contraseña a su DNI?">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">Restablecer contraseña</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('accesos.estudiante', $estudiante) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-key me-1"></i> Crear acceso</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        {{-- Asistencia --}}
        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-check2-square me-1"></i> Asistencia @if ($ciclo) {{ $ciclo->anio }} @endif</span>
                @if ($asistencia['porcentaje'] !== null)
                    <span class="badge {{ $asistencia['porcentaje'] < 85 ? 'text-bg-danger' : 'text-bg-success' }}">{{ number_format($asistencia['porcentaje'], 1, ',', '') }} %</span>
                @endif
            </div>
            <div class="card-body">
                @php $num = fn ($v) => rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ','); @endphp
                <div class="row text-center g-2 mb-2">
                    <div class="col-3"><div class="fs-5 fw-semibold">{{ $asistencia['dias'] }}</div><div class="small text-muted">Días</div></div>
                    <div class="col-3"><div class="fs-5 fw-semibold text-danger">{{ $num($asistencia['inasistencias']) }}</div><div class="small text-muted">Inasistencias</div></div>
                    <div class="col-3"><div class="fs-5 fw-semibold">{{ $num($asistencia['justificadas']) }}</div><div class="small text-muted">Justificadas</div></div>
                    <div class="col-3"><div class="fs-5 fw-semibold">{{ $num($asistencia['injustificadas']) }}</div><div class="small text-muted">Injustificadas</div></div>
                </div>

                @php $faltas = $asistencia['registros']->where('estado', '!=', 'presente'); @endphp
                @if ($faltas->isNotEmpty())
                    <div class="small fw-semibold mt-3 mb-1">Inasistencias, tardes y retiros</div>
                    <ul class="list-group list-group-flush small">
                        @foreach ($faltas->take(15) as $f)
                            <li class="list-group-item px-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <span>
                                    {{ $f->fecha->format('d/m') }} ·
                                    <span class="text-{{ \App\Models\Asistencia::color($f->estado) }}">{{ $f->estado_label }}</span>
                                    @if ($f->justificada) <span class="badge text-bg-light border">Justificada{{ $f->motivo ? ': ' . $f->motivo : '' }}</span> @endif
                                </span>
                                @if (auth()->user()->rol->esGestion() || auth()->user()->rol === \App\Enums\Rol::Preceptor)
                                    <form method="POST" action="{{ route('asistencia.justificar', $f) }}" class="d-flex gap-1">
                                        @csrf @method('PATCH')
                                        @if ($f->justificada)
                                            <input type="hidden" name="justificada" value="0">
                                            <button class="btn btn-sm btn-link py-0">Quitar justificación</button>
                                        @else
                                            <input type="hidden" name="justificada" value="1">
                                            <input type="text" name="motivo" class="form-control form-control-sm" placeholder="Motivo" maxlength="200" style="width: 140px">
                                            <button class="btn btn-sm btn-outline-primary py-0">Justificar</button>
                                        @endif
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @elseif ($asistencia['dias'])
                    <p class="small text-success mb-0"><i class="bi bi-check-circle"></i> Sin inasistencias.</p>
                @else
                    <p class="small text-muted mb-0">Todavía no hay asistencia registrada.</p>
                @endif

                @if ($porMateria->isNotEmpty())
                    <div class="small fw-semibold mt-3 mb-1">Por materia <span class="text-muted fw-normal">(desde GestiónAula)</span></div>
                    <table class="table table-sm small mb-0">
                        <thead><tr><th>Espacio</th><th class="text-center">Clases</th><th class="text-center">Faltas</th><th class="text-center">%</th></tr></thead>
                        <tbody>
                            @foreach ($porMateria as $m)
                                <tr>
                                    <td>{{ $m->espacio->nombre }}</td>
                                    <td class="text-center">{{ $m->clases }}</td>
                                    <td class="text-center">{{ $num($m->faltas) }}</td>
                                    <td class="text-center {{ $m->porcentaje < 75 ? 'text-danger fw-semibold' : '' }}">{{ number_format($m->porcentaje, 1, ',', '') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Inscripciones --}}
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-journal-check me-1"></i> Inscripciones</div>
            @if ($estudiante->inscripciones->isEmpty())
                <div class="card-body small text-muted">Todavía no tiene inscripciones.</div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ciclo</th>
                                <th>Curso / carrera</th>
                                <th class="d-none d-md-table-cell">Condición</th>
                                <th class="text-center">Estado</th>
                                @if ($puedeEditar) <th class="text-end"></th> @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estudiante->inscripciones->sortByDesc(fn ($i) => $i->ciclo->anio) as $i)
                                <tr>
                                    <td>{{ $i->ciclo->anio }}</td>
                                    <td>
                                        <div>{{ $i->destino }}</div>
                                        <div class="small text-muted">
                                            {{ $i->nivel->nivel->labelCorto() }}
                                            @if ($i->curso && $i->plan) · {{ $i->plan->nombre }} @endif
                                            · desde {{ $i->fecha->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell small">{{ $i->condicion_label }}</td>
                                    <td class="text-center">
                                        <span class="badge text-bg-{{ $i->estado_color }}">{{ $i->estado_label }}</span>
                                        @if ($i->fecha_baja) <div class="small text-muted">{{ $i->fecha_baja->format('d/m/Y') }}</div> @endif
                                    </td>
                                    @if ($puedeEditar)
                                        <td class="text-end">
                                            <a href="{{ route('inscripciones.edit', $i) }}" class="btn btn-sm btn-outline-secondary" title="Gestionar"><i class="bi bi-pencil"></i></a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
