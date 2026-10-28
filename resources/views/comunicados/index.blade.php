@extends('layouts.app')

@section('titulo', 'Comunicados')

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Comunicados</h1>
        <p class="text-muted mb-0">Avisos a familias, estudiantes y docentes, con seguimiento de lectura.</p>
    </div>
    <a href="{{ route('comunicados.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nuevo comunicado</a>
</div>

<form method="GET" class="card mb-3">
    <div class="card-body row g-2">
        <div class="col-md-5">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por título">
        </div>
        <div class="col-md-5">
            <div class="btn-group w-100">
                @foreach (['' => 'Todos', 'publicados' => 'Publicados', 'borradores' => 'Borradores'] as $v => $t)
                    <a href="{{ route('comunicados.index', array_filter(['q' => request('q'), 'estado' => $v])) }}"
                       class="btn btn-sm {{ (string) request('estado') === $v ? 'btn-primary' : 'btn-outline-primary' }}">{{ $t }}</a>
                @endforeach
            </div>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100"><i class="bi bi-search me-1"></i> Buscar</button>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Comunicado</th>
                    <th class="d-none d-md-table-cell">Destino</th>
                    <th class="d-none d-lg-table-cell">Publicado</th>
                    <th class="text-center">Leído</th>
                    <th class="text-center d-none d-sm-table-cell">Confirmado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comunicados as $c)
                    @php
                        $pctLeido = $c->destinatarios_count ? round($c->leidos_count * 100 / $c->destinatarios_count) : 0;
                    @endphp
                    <tr role="button" onclick="window.location='{{ route('comunicados.show', $c) }}'">
                        <td>
                            <a href="{{ route('comunicados.show', $c) }}" class="fw-semibold text-body text-decoration-none">{{ $c->titulo }}</a>
                            @if ($c->adjunto_path) <i class="bi bi-paperclip text-muted" title="Con adjunto"></i> @endif
                            <div class="small text-muted">{{ $c->autor?->nombre_completo }}</div>
                        </td>
                        <td class="d-none d-md-table-cell small">{{ \Illuminate\Support\Str::limit($c->descripcion_destino, 70) }}</td>
                        <td class="d-none d-lg-table-cell small text-nowrap">
                            @if ($c->estaPublicado())
                                {{ $c->publicado_at->format('d/m/Y H:i') }}
                            @else
                                <span class="badge text-bg-warning">Borrador</span>
                            @endif
                        </td>
                        <td class="text-center" style="min-width: 110px">
                            @if ($c->estaPublicado())
                                <div class="small">{{ $c->leidos_count }} / {{ $c->destinatarios_count }}</div>
                                <div class="progress" style="height: 5px">
                                    <div class="progress-bar" style="width: {{ $pctLeido }}%"></div>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center d-none d-sm-table-cell">
                            @if ($c->estaPublicado() && $c->requiere_confirmacion)
                                {{ $c->confirmados_count }} / {{ $c->destinatarios_count }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Todavía no hay comunicados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($comunicados->hasPages())
        <div class="card-footer bg-white">{{ $comunicados->links() }}</div>
    @endif
</div>

@endsection
