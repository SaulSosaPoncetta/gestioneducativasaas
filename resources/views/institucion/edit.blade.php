@extends('layouts.app')

@section('titulo', 'Datos de la institución')

@section('contenido')

<div class="mb-3">
    <h1 class="h3 mb-0">Datos de la institución</h1>
    <p class="text-muted mb-0">Datos institucionales y niveles educativos que ofrece.</p>
</div>

<form method="POST" action="{{ route('institucion.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-building me-1"></i> Datos</div>
                <div class="card-body">
                    @include('instituciones.partials.campos')
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-layers me-1"></i> Niveles que ofrece</div>
                <div class="card-body">
                    <p class="small text-muted">Al destildar un nivel queda inactivo pero no se borra su información.</p>
                    @include('instituciones.partials.niveles')
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar cambios</button>
    </div>
</form>

@endsection
