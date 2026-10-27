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

{{-- Integración con GestiónAula --}}
<div class="card mt-4">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-plug me-1"></i> Integración con GestiónAula</div>
    <div class="card-body">
        <p class="small text-muted">
            Los docentes toman asistencia en GestiónAula y se envía acá por materia. Los estudiantes se identifican por DNI
            y el docente por su DNI (tiene que estar cargado en Docentes con sus asignaciones).
        </p>

        @if (session('token_generado'))
            <div class="alert alert-warning">
                <div class="fw-semibold mb-1"><i class="bi bi-key me-1"></i> Token generado. Copialo ahora: no se vuelve a mostrar.</div>
                <div class="input-group">
                    <input type="text" class="form-control font-monospace" id="tokenGenerado" value="{{ session('token_generado') }}" readonly>
                    <button class="btn btn-outline-secondary" type="button"
                            onclick="navigator.clipboard.writeText(document.getElementById('tokenGenerado').value); this.innerText='Copiado'">Copiar</button>
                </div>
            </div>
        @endif

        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            @if ($institucion->tieneApiToken())
                <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i> Token activo desde {{ $institucion->api_token_creado_at->format('d/m/Y H:i') }}</span>
                <form method="POST" action="{{ route('institucion.token') }}" data-confirmar="Se va a generar un token nuevo y el actual deja de funcionar. ¿Continuar?">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary">Regenerar token</button>
                </form>
                <form method="POST" action="{{ route('institucion.token.revocar') }}" data-confirmar="¿Revocar el token? GestiónAula no va a poder enviar más datos.">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Revocar</button>
                </form>
            @else
                <span class="badge text-bg-secondary">Sin token</span>
                <form method="POST" action="{{ route('institucion.token') }}">
                    @csrf
                    <button class="btn btn-sm btn-primary"><i class="bi bi-key me-1"></i> Generar token</button>
                </form>
            @endif
        </div>

        <details class="small">
            <summary class="fw-semibold">Datos técnicos de la API</summary>
            <div class="mt-2">
                <p class="mb-1">Encabezado: <code>Authorization: Bearer &lt;token&gt;</code></p>
                <p class="mb-1">Probar conexión: <code>GET {{ url('/api/v1/ping') }}</code></p>
                <p class="mb-1">Enviar asistencia: <code>POST {{ url('/api/v1/asistencias') }}</code></p>
<pre class="bg-light border rounded p-2 mb-0"><code>{
  "docente_dni": "27111333",
  "fecha": "{{ today()->toDateString() }}",
  "espacio": "Matemática",
  "referencia": "clase-1234",
  "asistencias": [
    {"dni": "54001370", "estado": "presente"},
    {"dni": "54001507", "estado": "ausente", "observacion": "Avisó la familia"}
  ]
}</code></pre>
                <p class="mt-1 mb-3 text-muted">Estados: presente, ausente, tarde, retiro. Reenviar la misma clase (misma fecha y referencia) la actualiza.</p>

                <p class="mb-1">Enviar calificaciones: <code>POST {{ url('/api/v1/calificaciones') }}</code></p>
<pre class="bg-light border rounded p-2 mb-0"><code>{
  "docente_dni": "27111333",
  "espacio": "Matemática",
  "periodo": 1,
  "calificaciones": [
    {"dni": "54001370", "nota": "8"},
    {"dni": "54001507", "nota": "6,5", "observacion": "Reforzar fracciones"}
  ]
}</code></pre>
                <p class="mt-1 mb-0 text-muted">
                    "periodo": número de orden (1, 2, 3) o nombre ("2° Trimestre"). Nota numérica de 1 a 10 o conceptual (E, MB, B, R, I)
                    según la escala del nivel; en nivel inicial se envía "informe" con el texto. Los períodos cerrados no reciben notas
                    y las corregidas en secretaría no se pisan.
                </p>
            </div>
        </details>
    </div>
</div>

@endsection
