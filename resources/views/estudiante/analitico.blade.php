@extends('layouts.app')

@section('titulo', 'Mi analítico')

@push('estilos')
<style>@media print { body { background: #fff !important; } .card { box-shadow: none !important; border: 1px solid #999 !important; } }</style>
@endpush

@section('contenido')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
    <h1 class="h3 mb-0">Mi analítico</h1>
    <div class="d-flex gap-2">
        @if ($carreras->count() > 1)
            <form method="GET">
                <select name="plan" class="form-select" onchange="this.form.submit()">
                    @foreach ($carreras as $c)
                        <option value="{{ $c->id }}" @selected($plan?->id === $c->id)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </form>
        @endif
        <button type="button" class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
    </div>
</div>

<div class="alert alert-light border small no-print"><i class="bi bi-info-circle me-1"></i> Es una constancia informativa. El analítico oficial lo emite la secretaría.</div>

@include('analitico.partials.contenido')

@endsection
