<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Verificar constancia · {{ config('app.name') }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-body-tertiary">
<div class="container py-5" style="max-width: 620px">
    <div class="text-center mb-4">
        <div class="login-logo mb-2"><i class="bi bi-patch-check"></i></div>
        <h1 class="h4 mb-0">Verificación de constancias</h1>
        <p class="text-muted">Ingresá el código impreso al pie de la constancia.</p>
    </div>

    <form method="GET" action="{{ route('constancias.verificar.form') }}" class="d-flex gap-2 mb-4">
        <input type="text" name="codigo" class="form-control text-uppercase" placeholder="XXXX-XXXX-XXXX"
               value="{{ $codigo ? implode('-', str_split($codigo, 4)) : '' }}" required maxlength="20" autocomplete="off">
        <button class="btn btn-primary text-nowrap"><i class="bi bi-search me-1"></i> Verificar</button>
    </form>

    @if ($codigo)
        @if (! $constancia)
            <div class="alert alert-danger">
                <i class="bi bi-x-octagon-fill me-1"></i>
                <strong>No encontramos una constancia con ese código.</strong> Revisá que esté bien escrito.
            </div>
        @else
            @php
                $d = $constancia->datos;
                $doc = $d['estudiante']['documento'];
                // Se muestran solo los últimos 3 dígitos del documento.
                $docOculto = preg_replace('/\d(?=(?:\D*\d){3})/', '*', $doc);
            @endphp
            <div class="card border-{{ $constancia->estaAnulada() ? 'danger' : 'success' }} border-2">
                <div class="card-body">
                    @if ($constancia->estaAnulada())
                        <div class="text-danger fw-bold fs-5 mb-2"><i class="bi bi-x-circle-fill me-1"></i> Constancia ANULADA</div>
                        <p class="small">Anulada el {{ $constancia->anulada_at->format('d/m/Y') }}. No tiene validez.</p>
                    @else
                        <div class="text-success fw-bold fs-5 mb-2"><i class="bi bi-check-circle-fill me-1"></i> Constancia válida</div>
                    @endif
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Institución</dt><dd class="col-sm-8">{{ $constancia->institucion->nombre }}</dd>
                        <dt class="col-sm-4">Tipo</dt><dd class="col-sm-8">{{ $constancia->tipo_label }}</dd>
                        <dt class="col-sm-4">Número</dt><dd class="col-sm-8">{{ $constancia->numero_label }}</dd>
                        <dt class="col-sm-4">Estudiante</dt><dd class="col-sm-8">{{ $d['estudiante']['apellido'] }}, {{ $d['estudiante']['nombre'] }}</dd>
                        <dt class="col-sm-4">Documento</dt><dd class="col-sm-8">{{ $docOculto }}</dd>
                        <dt class="col-sm-4">Detalle</dt>
                        <dd class="col-sm-8">
                            @if ($constancia->tipo === 'examen')
                                {{ $d['espacio'] }} · {{ \Illuminate\Support\Carbon::parse($d['fecha'])->format('d/m/Y') }}
                            @else
                                {{ $d['destino'] }} · ciclo {{ $d['ciclo'] }}
                            @endif
                        </dd>
                        <dt class="col-sm-4">Emitida</dt><dd class="col-sm-8">{{ $constancia->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
            <p class="small text-muted mt-3">Compará estos datos con los de la constancia impresa.</p>
        @endif
    @endif
</div>
</body>
</html>
