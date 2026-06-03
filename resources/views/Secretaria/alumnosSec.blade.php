<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Alumno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filtro.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/alumnosSec.css') }}">
</head>

<body>
    <button class="mobile-menu-btn" id="menu-btn">
        <i class="ri-menu-line"></i>
    </button>

    @include('partials.sidebar')

    <div class="main">

        <!-- HEADER -->
        <div class="top-bar">
            <h1>Buscar Alumno</h1>
            <button class="btn-primary" onclick="window.location.href='{{ route('ingresarAlumno') }}'">
                Agregar Alumno
            </button>
        </div>

        <!-- FILTROS -->
        <form method="GET" action="{{ route('alumnos.index') }}" id="form-filtros">
            @csrf

            <div class="card-container">
                <div class="form-grid">

                    <input
                        class="form-grid"
                        type="text"
                        name="nombre_apellido"
                        placeholder="Nombre y Apellido"
                        value="{{ $filtros['nombre_apellido'] ?? '' }}"
                    >

                    <input
                        class="form-grid"
                        type="text"
                        name="dni"
                        placeholder="DNI"
                        value="{{ $filtros['dni'] ?? '' }}"
                    >

                    <div class="row-small">
                        <select name="anio">
                            <option value="">Año</option>
                            @for ($y = date('Y'); $y >= 2000; $y--)
                                <option value="{{ $y }}" {{ ($filtros['anio'] ?? '') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>

                        <input
                            type="number"
                            name="padron"
                            placeholder="Padrón"
                            value="{{ $filtros['padron'] ?? '' }}"
                        >
                    </div>

                </div>
            </div>

            <!-- BOTONES -->
            <div class="form-actions">
                <button type="button" class="btn-secondary" id="btn-limpiar">Limpiar</button>
                <button type="submit" class="btn-primary">Filtrar</button>
            </div>

        </form>

        <!-- TABLA DE RESULTADOS -->
        @if ($estudiantes !== null)
            <div class="table-container">
                @if ($estudiantes->count() > 0)
                    <table class="cursada-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>DNI</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Perfil</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estudiantes as $est)
                                <tr>
                                    <td>{{ $est->usuario->apellido }}, {{ $est->usuario->nombre }}</td>
                                    <td>
                                        {{-- DNI se carga por relación si existe, sino guión --}}
                                        {{ $est->usuario->dnis->first()?->dni ?? '—' }}
                                    </td>
                                    <td>{{ $est->usuario->email }}</td>
                                    <td>
                                        {{ $est->usuario->telefonos->first()?->telefono ?? '—' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('alumnos.show', $est->id_usuario) }}">
                                            <button class="btn-primary" type="button">Ver</button>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- PAGINACIÓN -->
                    <div class="pagination-container">
                        {{ $estudiantes->appends($filtros)->links() }}
                    </div>

                @else
                    <div class="sin-resultados">
                        <i class="ri-search-line"></i>
                        <p>No se encontraron alumnos con los filtros aplicados.</p>
                    </div>
                @endif
            </div>
        @endif

    </div>

    @include('partials.footer')

    <script>
        // Botón limpiar: limpia los inputs y redirige sin filtros
        document.getElementById('btn-limpiar').addEventListener('click', function () {
            window.location.href = '{{ route('alumnos.index') }}';
        });
    </script>

</body>
</html>
