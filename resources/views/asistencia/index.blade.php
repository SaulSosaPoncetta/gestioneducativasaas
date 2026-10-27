@extends('layouts.app')

@section('titulo', 'Asistencia diaria')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Asistencia diaria</h1>
        <p class="text-muted mb-0">{{ ucfirst($fecha->translatedFormat('l j \d\e F \d\e Y')) }}</p>
    </div>
    <form method="GET" class="d-flex align-items-center gap-2">
        @if ($todos) <input type="hidden" name="todos" value="1"> @endif
        <a href="{{ route('asistencia.index', ['fecha' => $fecha->copy()->subDay()->toDateString(), 'todos' => $todos ?: null]) }}" class="btn btn-outline-secondary" title="Día anterior"><i class="bi bi-chevron-left"></i></a>
        <input type="date" name="fecha" value="{{ $fecha->toDateString() }}" max="{{ today()->toDateString() }}" class="form-control" onchange="this.form.submit()">
        @unless ($fecha->isToday())
            <a href="{{ route('asistencia.index', ['fecha' => $fecha->copy()->addDay()->toDateString(), 'todos' => $todos ?: null]) }}" class="btn btn-outline-secondary" title="Día siguiente"><i class="bi bi-chevron-right"></i></a>
            <a href="{{ route('asistencia.index', ['todos' => $todos ?: null]) }}" class="btn btn-outline-primary">Hoy</a>
        @endunless
    </form>
</div>

@if ($fecha->isWeekend())
    <div class="alert alert-info py-2"><i class="bi bi-info-circle me-1"></i> Es fin de semana.</div>
@endif

@if ($esPreceptor)
    <div class="mb-3">
        @if ($todos)
            <a href="{{ route('asistencia.index', ['fecha' => $fecha->toDateString()]) }}" class="small">Ver solo mis cursos</a>
        @else
            <span class="small text-muted">Se muestran tus cursos y los que no tienen preceptor/a.</span>
            <a href="{{ route('asistencia.index', ['fecha' => $fecha->toDateString(), 'todos' => 1]) }}" class="small ms-1">Ver todos</a>
        @endif
    </div>
@endif

@if (! $ciclo)
    <div class="alert alert-warning">No hay ciclo lectivo.</div>
@elseif ($cursos->isEmpty())
    <div class="alert alert-info">No hay cursos para mostrar.</div>
@else
    @foreach ($cursos as $lista)
        <h2 class="h6 text-muted text-uppercase mt-3 mb-2">{{ $lista->first()->nivel->nombre }}</h2>
        <div class="row g-2 mb-2">
            @foreach ($lista as $curso)
                @php $toma = $tomas->get($curso->id); @endphp
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="card h-100 {{ $toma ? 'border-start border-4 border-success' : '' }}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold">{{ $curso->nombre_corto }}</div>
                                    <div class="small text-muted">{{ $curso->turno_label }} · {{ $curso->inscripciones_activas_count }} est.</div>
                                </div>
                                @if ($toma)
                                    <span class="badge text-bg-success"><i class="bi bi-check-lg"></i></span>
                                @endif
                            </div>
                            <div class="small mt-2" style="min-height: 1.3rem">
                                @if ($toma)
                                    <span class="text-danger">{{ $toma->ausentes }} {{ $toma->ausentes === 1 ? 'ausente' : 'ausentes' }}</span>
                                    @if ($toma->tardes) · <span class="text-warning-emphasis">{{ $toma->tardes }} T/R</span> @endif
                                @else
                                    <span class="text-muted">Sin tomar</span>
                                @endif
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <a href="{{ route('asistencia.tomar', ['curso' => $curso, 'fecha' => $fecha->toDateString()]) }}"
                                   class="btn btn-sm {{ $toma ? 'btn-outline-primary' : 'btn-primary' }} flex-grow-1">
                                    {{ $toma ? 'Ver / editar' : 'Tomar' }}
                                </a>
                                <a href="{{ route('asistencia.mensual', ['curso' => $curso, 'mes' => $fecha->format('Y-m')]) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Planilla mensual"><i class="bi bi-calendar3"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
@endif

@endsection
