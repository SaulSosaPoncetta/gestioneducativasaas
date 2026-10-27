<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta · {{ $mesa->espacio->nombre }}</title>
    @vite(['resources/scss/app.scss'])
    <style>
        body { background: #fff; font-size: 13px; }
        .acta { max-width: 820px; margin: 24px auto; }
        table td, table th { padding: 4px 8px !important; }
        @page { margin: 14mm; }
    </style>
</head>
<body>
<div class="acta">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <div class="fw-bold fs-5">{{ $institucionActual->nombre }}</div>
            <div>{{ $mesa->espacio->plan->nivel->nombre }} · CUE {{ $institucionActual->cue }}</div>
        </div>
        <div class="text-end">
            <div class="fw-bold">ACTA DE EXAMEN</div>
            @if ($mesa->libro || $mesa->folio) <div>Libro {{ $mesa->libro }} · Folio {{ $mesa->folio }}</div> @endif
        </div>
    </div>

    <table class="table table-bordered mb-3">
        <tr><th style="width: 160px">Carrera</th><td>{{ $mesa->espacio->plan->nombre }}</td></tr>
        <tr><th>Unidad curricular</th><td>{{ $mesa->espacio->nombre }} ({{ $mesa->espacio->grado }}° año)</td></tr>
        <tr><th>Llamado</th><td>{{ $mesa->llamado }} · {{ $mesa->fecha->format('d/m/Y H:i') }}</td></tr>
        <tr><th>Tribunal</th><td>{{ $mesa->presidente?->nombre_completo ?? '' }}{{ $mesa->vocales ? ', ' . $mesa->vocales : '' }}</td></tr>
    </table>

    <table class="table table-bordered">
        <thead>
            <tr><th>#</th><th>Apellido y nombre</th><th>DNI</th><th>Condición</th><th class="text-center">Nota</th><th>En letras</th><th>Resultado</th></tr>
        </thead>
        <tbody>
            @php $letras = [1 => 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve', 'diez']; @endphp
            @foreach ($inscriptos as $n => $i)
                <tr>
                    <td>{{ $n + 1 }}</td>
                    <td>{{ $i->estudiante->nombre_completo }}</td>
                    <td>{{ $i->estudiante->documento }}</td>
                    <td>{{ ucfirst($i->condicion) }}</td>
                    <td class="text-center">{{ $i->nota !== null ? rtrim(rtrim(number_format($i->nota, 2, ',', ''), '0'), ',') : ($i->resultado === 'ausente' ? 'A' : '') }}</td>
                    <td>{{ $i->nota !== null && fmod($i->nota, 1) == 0 ? ($letras[(int) $i->nota] ?? '') : '' }}</td>
                    <td>{{ $i->resultado ? \App\Models\MesaInscripcion::RESULTADOS[$i->resultado] : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php $r = $inscriptos->countBy('resultado'); @endphp
    <p>Inscriptos: {{ $inscriptos->count() }} · Aprobados: {{ $r['aprobado'] ?? 0 }} · Desaprobados: {{ $r['desaprobado'] ?? 0 }} · Ausentes: {{ $r['ausente'] ?? 0 }}</p>

    <div class="d-flex justify-content-around mt-5 pt-5 text-center">
        <div style="width: 28%; border-top: 1px solid #000">Presidente/a</div>
        <div style="width: 28%; border-top: 1px solid #000">Vocal</div>
        <div style="width: 28%; border-top: 1px solid #000">Vocal</div>
    </div>
</div>
<script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
