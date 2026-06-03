<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cursada</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/cursadaSec.css') }}">
</head>

<body>
    <button class="mobile-menu-btn" id="menu-btn">
    <i class="ri-menu-line"></i>
    </button>

@include('partials.sidebar')

<div class="main">

    <!-- TÍTULO -->
    <div class="top-bar">
        <h1>Buscar por carrera</h1>
    </div>

    <!-- FILTROS -->
    <div class="filter-card">
        <div class="filter-grid">

            <select id="sel-nombre">
                <option value="">Nombre</option>
                {{-- @foreach($carreras as $c) --}}
                {{-- <option value="{{ $c->carrera_id }}">{{ $c->nombre }}</option> --}}
                {{-- @endforeach --}}
            </select>

            <select id="sel-plan">
                <option value="">Plan de estudio</option>
            </select>

        </div>

        <div class="filter-actions">
            <button class="btn-secondary" id="btn-limpiar">Limpiar</button>
            <button class="btn-primary"   id="btn-filtrar" disabled>Filtrar</button>
        </div>
    </div>

    <!-- BOTONES DE ACCIÓN (deshabilitados hasta elegir carrera) -->
    <div class="action-buttons">
        <button class="btn-primary" disabled data-href="{{ route('materiaUP') }}">Nueva Materia</button>
        <button class="btn-primary" disabled data-href="{{ route('cargarNota') }}">Cargar Notas</button>
        <button class="btn-primary" disabled data-href="{{ route('alumnoUP') }}">Agregar Profesor</button>
        <button class="btn-primary" disabled data-href="{{ route('docenteUP') }}">Agregar Alumno</button>
    </div>

    <!-- TABLA / PLACEHOLDER -->
    <div class="table-area">

        {{-- Placeholder: visible por defecto --}}
        <div class="table-placeholder" id="table-placeholder">
            <i class="ri-search-line"></i>
            <p>Seleccioná una carrera y presioná <strong>Filtrar</strong> para ver las materias.</p>
        </div>

        {{-- Tabla: oculta por defecto, el backend la llenará --}}
        <div id="table-content" style="display: none;">
            {{-- Acá van a ir los bloques por año, generados por Blade con @foreach --}}
            {{-- Ejemplo de estructura para cuando lo conecten al backend: --}}
            {{--
            @foreach($añosPorCarrera as $año => $materias)
            <div class="year-block">
                <table class="cursada-table">
                    <thead>
                        <tr>
                            <th>Espacio</th>
                            <th>Año</th>
                            <th>Profesores</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materias as $m)
                        <tr>
                            <td>{{ $m->nombre }}</td>
                            <td>{{ $año }}°</td>
                            <td>{{ $m->profesores->pluck('apellido')->join(', ') }}</td>
                            <td>
                                <button class="btn-lupa" title="Ver detalle">
                                    <i class="ri-search-line"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
            --}}

            {{-- TABLA DE EJEMPLO (sacar cuando conecten el backend) --}}
            <div class="year-block">
                <table class="cursada-table">
                    <thead>
                        <tr>
                            <th>Espacio</th>
                            <th>Año</th>
                            <th>Profesores</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sistemas y Organizaciones</td>
                            <td>1°</td>
                            <td>Letier -</td>
                            <td><button class="btn-lupa" title="Ver detalle"><i class="ri-search-line"></i></button></td>
                        </tr>
                        <tr>
                            <td>Prácticas Profesionalizantes I</td>
                            <td>1°</td>
                            <td>Chiramberro -</td>
                            <td><button class="btn-lupa" title="Ver detalle"><i class="ri-search-line"></i></button></td>
                        </tr>
                        <tr>
                            <td>Análisis Matemático I</td>
                            <td>1°</td>
                            <td>Beratz -</td>
                            <td><button class="btn-lupa" title="Ver detalle"><i class="ri-search-line"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="year-block">
                <table class="cursada-table">
                    <thead>
                        <tr>
                            <th>Espacio</th>
                            <th>Año</th>
                            <th>Profesores</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sistemas Operativos</td>
                            <td>2°</td>
                            <td>Letier -</td>
                            <td><button class="btn-lupa" title="Ver detalle"><i class="ri-search-line"></i></button></td>
                        </tr>
                        <tr>
                            <td>Prácticas Profesionalizantes II</td>
                            <td>2°</td>
                            <td>Chiramberro -</td>
                            <td><button class="btn-lupa" title="Ver detalle"><i class="ri-search-line"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@include('partials.footer')

<div class="overlay" id="overlay"></div>

<script src="{{ asset('js/Secretaria/cursadaPrueba.js') }}" defer></script>

</body>
</html>
