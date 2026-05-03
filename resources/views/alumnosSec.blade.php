<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar por carrera</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/homeSec.css') }}">
    <link rel="stylesheet" href="{{ asset('css/alumnosSec.css') }}">
</head>

<body>

@include('partials.sidebar')

<div class="main">

    <!-- HEADER -->
    <div class="top-bar">
        <h1>Buscar por carrera</h1>
        <button class="btn-primary">Agregar Alumno</button>
    </div>

    <!-- CARD -->
    <div class="card-container">

        <h2>Carrera</h2>

        <div class="form-grid">

            <select>
                <option>Nombre</option>
            </select>

            <select>
                <option>Materia</option>
            </select>

            <select>
                <option>Plan de estudio</option>
            </select>

            <div class="row-small">
                <select>
                    <option>Año</option>
                </select>

                <select>
                    <option>Padrón</option>
                </select>
            </div>

        </div>

    </div>

    <!-- BOTONES -->
    <div class="form-actions">
        <button class="btn-secondary">Limpiar</button>
        <button class="btn-primary">Filtrar</button>
    </div>

</div>

@include('partials.footer')

</body>
</html>