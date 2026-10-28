<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Constancia {{ $constancia->numero_label }} · {{ $constancia->datos['estudiante']['apellido'] }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <style>
        body { background: #f1ece8; }
        .hoja { max-width: 800px; margin: 24px auto; background: #fff; padding: 48px 56px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .constancia .texto p { font-size: 1.08rem; line-height: 1.9; text-align: justify; text-indent: 3em; }
        .firma { border-top: 1px solid #333; margin: 0 2rem; padding-top: .35rem; }
        .sello-anulada {
            position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 6rem; font-weight: 800; color: rgba(185, 28, 28, .25); border: 8px solid rgba(185, 28, 28, .25);
            padding: 0 2rem; pointer-events: none; letter-spacing: .1em;
        }
        @media (max-width: 576px) { .hoja { padding: 24px 18px; margin: 0; } .sello-anulada { font-size: 3rem; } }
        @media print {
            body { background: #fff; }
            .hoja { box-shadow: none; margin: 0; padding: 0; max-width: none; }
            .no-print { display: none !important; }
            @page { margin: 18mm 20mm; }
        }
    </style>
</head>
<body>
    <div class="no-print d-flex flex-wrap justify-content-center gap-2 py-3">
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir / guardar PDF</button>
        <a href="{{ $volver }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @if ($constancia->estaAnulada())
        <div class="no-print alert alert-danger mx-auto" style="max-width: 800px">
            Constancia anulada el {{ $constancia->anulada_at->format('d/m/Y') }}{{ $constancia->motivo_anulacion ? ': ' . $constancia->motivo_anulacion : '' }}.
        </div>
    @endif

    <div class="hoja">
        @include('constancias.partials.documento')
    </div>
</body>
</html>
