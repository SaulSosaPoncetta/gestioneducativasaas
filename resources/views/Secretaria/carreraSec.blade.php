<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carreras</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/carreraSec.css') }}">
</head>

<body>
    <button class="mobile-menu-btn" id="menu-btn">
    <i class="ri-menu-line"></i>
    </button>

@include('partials.sidebar')

<div class="main">

    <div class="top-bar">
        <h1>Carreras</h1>
    </div>

    <!-- BOTONES DE ACCIÓN -->
    <div class="action-buttons">
        <button class="btn-primary"   data-href="#">Agregar</button>
        <button class="btn-primary"   data-href="#">Plan de estudio</button>
        <button class="btn-primary"   data-href="#">Egresados</button>
        <button class="btn-primary"   data-href="#">Matriculaciones</button>
        <button class="btn-primary"   data-href="#">Registros académicos</button>
    </div>

    <!-- TABLA / PLACEHOLDER -->
    <div class="table-area">

        @if(isset($carreras) && $carreras->count() > 0)

            <table class="carrera-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Plan de estudios actual</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($carreras as $carrera)
                    <tr>
                        <td>{{ $carrera->nombre }}</td>
                        <td>{{ $carrera->planVigente->nombre ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        @else

            <div class="table-placeholder">
                <i class="ri-booklet-line"></i>
                <p>No hay carreras cargadas. Usá <strong>Agregar</strong> para crear la primera.</p>
            </div>

        @endif

    </div>

</div>

@include('partials.footer')

<div class="overlay" id="overlay"></div>

<script src="{{ asset('js/Secretaria/carreraSec.js') }}" defer></script>

</body>
</html>
