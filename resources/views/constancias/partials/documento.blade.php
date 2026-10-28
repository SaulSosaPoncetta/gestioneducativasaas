{{--
    Cuerpo de la constancia. Usa solo los datos copiados al emitir ($constancia->datos),
    así se reimprime igual aunque después cambie el legajo.
    Requiere: $constancia, $institucion
--}}
@php
    use App\Support\NumeroEnLetras;
    use App\Services\ConstanciaService;

    $d = $constancia->datos;
    $est = $d['estudiante'];
    [$art, $alumno, $interesado] = ConstanciaService::articulo($est);
    $emitida = $constancia->created_at;
    $pedido = ($d['solicitante'] ?? null) === 'familia' ? 'de su familia' : $interesado;
    $lugar = $d['lugar'] ?: $institucion->localidad;
@endphp

<div class="constancia position-relative">
    @if ($constancia->estaAnulada())
        <div class="sello-anulada">ANULADA</div>
    @endif

    <header class="d-flex align-items-center gap-3 pb-3 mb-4 border-bottom border-2">
        @if ($institucion->logo_url)
            <img src="{{ $institucion->logo_url }}" alt="" style="height: 72px; width: auto">
        @endif
        <div class="flex-grow-1">
            <div class="fw-bold fs-5 lh-sm">{{ $institucion->nombre }}</div>
            <div class="small text-muted">
                {{ collect([$institucion->domicilio, $institucion->localidad, $institucion->provincia])->filter()->join(' · ') }}
            </div>
            <div class="small text-muted">
                {{ collect([$institucion->cue ? 'CUE ' . $institucion->cue : null, $institucion->telefono, $institucion->email])->filter()->join(' · ') }}
            </div>
        </div>
        <div class="text-end small">
            <div class="fw-semibold">{{ $constancia->numero_label }}</div>
        </div>
    </header>

    <h1 class="text-center h4 fw-bold mb-4 text-uppercase" style="letter-spacing: .05em">
        Constancia de {{ $constancia->tipo === 'examen' ? 'examen' : 'alumno regular' }}
    </h1>

    @php
        $b = fn ($t) => '<strong>' . e($t) . '</strong>';
        $res = fn ($r) => $r ? ' (Res. ' . e($r) . ')' : '';
        $persona = e($art . ' ' . $alumno) . ' ' . $b(mb_strtoupper($est['apellido']) . ', ' . $est['nombre']) . ', ' . e($est['documento']);

        if ($constancia->tipo === 'alumno_regular') {
            $cuerpo = $d['superior']
                ? "es {$alumno} regular de la carrera " . $b($d['destino']) . $res($d['resolucion'])
                    . ' que se dicta en esta institución, en condición de ' . e(mb_strtolower($d['condicion']))
                    . ', durante el ciclo lectivo ' . e($d['ciclo']) . '.'
                : "es {$alumno} regular de " . $b($d['destino'])
                    . ($d['turno'] ? ' (turno ' . e(mb_strtolower($d['turno'])) . ')' : '')
                    . ' del ' . e($d['nivel']) . ' de este establecimiento, durante el ciclo lectivo ' . e($d['ciclo']) . '.';
        } else {
            $fecha = \Illuminate\Support\Carbon::parse($d['fecha']);
            $nota = $d['incluir_nota'] && $d['nota'] !== null
                ? ', obteniendo la calificación de ' . $b(rtrim(rtrim(number_format($d['nota'], 2, ',', ''), '0'), ',')
                    . ' (' . NumeroEnLetras::nota((float) $d['nota']) . ')') . ' — ' . ($d['resultado'] === 'aprobado' ? 'Aprobado' : 'Desaprobado')
                : '';
            $cuerpo = 'de la carrera ' . $b($d['carrera']) . $res($d['resolucion'])
                . ', se presentó a rendir examen final de ' . $b($d['espacio']) . ' (' . e($d['grado']) . '° año)'
                . ' en condición de ' . e($d['condicion']) . ', el día ' . $fecha->format('d/m/Y')
                . ($fecha->format('H:i') !== '00:00' ? ' a las ' . $fecha->format('H:i') . ' h' : '')
                . ($d['llamado'] ? ' (' . e($d['llamado']) . ')' : '')
                . $nota . '.'
                . ($d['libro'] || $d['folio'] ? ' Consta en el Libro de Actas ' . e($d['libro']) . ', folio ' . e($d['folio']) . '.' : '');
        }

        $cierre = 'A pedido ' . e($pedido) . ' y para ser presentada ante ' . $b($constancia->presentar_ante ?: 'quien corresponda')
            . ', se extiende la presente' . ($lugar ? ' en ' . e($lugar) . ',' : '')
            . ' a los ' . NumeroEnLetras::entero($emitida->day) . ' días del mes de ' . NumeroEnLetras::MESES[$emitida->month]
            . ' de ' . NumeroEnLetras::entero($emitida->year) . '.';
    @endphp

    <div class="texto">
        <p>Se deja constancia de que {!! $persona !!}, {!! $cuerpo !!}</p>
        <p>{!! $cierre !!}</p>
    </div>

    <div class="row mt-5 pt-5 text-center small">
        <div class="col-6"><div class="firma">Firma y sello<br>Secretaría</div></div>
        <div class="col-6"><div class="firma">Firma y sello<br>Dirección</div></div>
    </div>

    <footer class="d-flex align-items-center gap-3 mt-5 pt-3 border-top small text-muted">
        <div data-qr="{{ $constancia->url_verificacion }}" style="width: 90px; height: 90px" data-qr-tamano="90"></div>
        <div>
            Verificá la validez de esta constancia escaneando el código QR o ingresando en
            <span class="text-body">{{ route('constancias.verificar.form') }}</span>
            con el código <strong class="text-body">{{ $constancia->codigo_label }}</strong>.
            <div>Emitida el {{ $emitida->format('d/m/Y H:i') }}{{ $constancia->origen === 'portal' ? ' desde el portal' : '' }}.</div>
        </div>
    </footer>
</div>
