@php
    /*
     * Menú del navbar: cada sección es un desplegable.
     * Cada ítem se muestra solo si su ruta existe y el usuario tiene el rol.
     * 'nivel' (opcional): se muestra solo si la institución ofrece alguno de
     * esos tipos (ej. finales solo con terciario/universitario).
     * A medida que se agregan módulos, sus ítems aparecen solos.
     */
    $gestion = ['admin', 'directivo', 'secretario'];

    $secciones = [
        'MiGestión' => [
            ['Instituciones', 'bi-buildings', 'instituciones.index', ['superadmin']],
        ],
        'Institución' => [
            ['Datos y niveles', 'bi-building-gear', 'institucion.edit', ['admin']],
            ['Ciclos lectivos', 'bi-calendar3', 'ciclos.index', $gestion],
            ['Usuarios', 'bi-person-badge', 'usuarios.index', ['admin']],
        ],
        'Académico' => [
            ['Planes de estudio', 'bi-diagram-3', 'planes.index', $gestion],
            ['Cursos y divisiones', 'bi-grid-3x3-gap', 'cursos.index', $gestion, 'cursos'],
            ['Asignaciones docentes', 'bi-person-check', 'asignaciones.index', $gestion],
        ],
        'Personas' => [
            ['Estudiantes', 'bi-people', 'estudiantes.index', [...$gestion, 'preceptor']],
            ['Docentes', 'bi-person-workspace', 'docentes.index', $gestion],
            ['Tutores / familias', 'bi-house-heart', 'tutores.index', [...$gestion, 'preceptor']],
        ],
        'Seguimiento' => [
            ['Asistencia diaria', 'bi-check2-square', 'asistencia.index', [...$gestion, 'preceptor'], 'cursos'],
            ['Calificaciones', 'bi-123', 'calificaciones.index', [...$gestion, 'preceptor']],
            ['Escalas de calificación', 'bi-sliders', 'calificaciones.configuracion', $gestion],
            ['Cursadas (superior)', 'bi-journal-bookmark', 'cursadas.index', $gestion, 'carreras'],
            ['Mesas de examen', 'bi-award', 'mesas.index', $gestion, 'carreras'],
        ],
        'Documentación' => [
            ['Constancias', 'bi-file-earmark-text', 'constancias.index', $gestion],
            ['Analíticos y boletines', 'bi-file-earmark-ruled', 'analiticos.index', $gestion],
            ['Auditoría', 'bi-clock-history', 'auditoria.index', ['admin']],
        ],
        'Mi espacio' => [
            ['Mis cursos', 'bi-easel', 'docente.cursos', ['docente']],
            ['Mis hijos', 'bi-person-hearts', 'tutor.hijos', ['tutor']],
            ['Mi cursada', 'bi-journal-check', 'estudiante.cursada', ['estudiante']],
            ['Inscripción a finales', 'bi-pencil-square', 'estudiante.finales', ['estudiante'], 'carreras'],
        ],
    ];

    $usuario     = auth()->user();
    $institucion = $institucionActual ?? null;
    $niveles     = $institucion?->nivelesActivos() ?? collect();

    $cumpleNivel = function (?string $condicion) use ($niveles) {
        return match ($condicion) {
            null       => true,
            'carreras' => $niveles->contains(fn ($n) => $n->usaCarreras()),
            'cursos'   => $niveles->contains(fn ($n) => $n->usaCursos()),
            default    => $niveles->contains(fn ($n) => $n->value === $condicion),
        };
    };

    $visible = function (array $item) use ($usuario, $institucion, $cumpleNivel) {
        [$label, $icono, $ruta, $roles] = $item;

        if (! Route::has($ruta) || ! $usuario->tieneRol(...$roles)) {
            return false;
        }

        // El superadmin solo ve los módulos de institución cuando entró a una.
        if ($usuario->esSuperAdmin() && ! $institucion && $roles !== ['superadmin']) {
            return false;
        }

        return $cumpleNivel($item[4] ?? null);
    };
@endphp

<ul class="navbar-nav me-auto mb-2 mb-lg-0">
    <li class="nav-item">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-house me-1"></i> Inicio
        </a>
    </li>

    @foreach ($secciones as $titulo => $items)
        @php
            $items = array_filter($items, $visible);
            $prefijos = array_map(fn ($i) => \Illuminate\Support\Str::beforeLast($i[2], '.') . '.*', $items);
            $seccionActiva = count($items) && request()->routeIs(...$prefijos);
        @endphp

        @if (count($items))
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle {{ $seccionActiva ? 'active' : '' }}" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    {{ $titulo }}
                </a>
                <ul class="dropdown-menu shadow-sm">
                    @foreach ($items as [$label, $icono, $ruta])
                        @php $prefijo = \Illuminate\Support\Str::beforeLast($ruta, '.'); @endphp
                        <li>
                            <a href="{{ route($ruta) }}" class="dropdown-item {{ request()->routeIs($prefijo . '.*') ? 'active' : '' }}">
                                <i class="bi {{ $icono }} me-2"></i>{{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif
    @endforeach
</ul>
