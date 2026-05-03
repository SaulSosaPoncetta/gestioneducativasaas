<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Alumno a Materia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/homeSec.css') }}">
    <link rel="stylesheet" href="{{ asset('css/estMatUpload.css') }}">
</head>

<body>

@include('partials.sidebar')

<div class="main">

    <div class="form-container">
        <h1>Agregar Alumno a Materia</h1>

        <form class="materia-form">

            <div class="form-grid">

                <label>Cursada</label>
                <select>
                    <option>Seleccione una cursada</option>
                </select>

                <label>Alumno</label>
                <select>
                    <option>Seleccione un alumno</option>
                </select>

                <label>Tipo</label>
                <select>
                    <option>Seleccione un tipo de estudiante</option>
                </select>

            </div>

            <div class="form-actions">
                <button class="btn-primary">Agregar</button>
                <button type="button" class="btn-secondary">Volver</button>
            </div>

        </form>
    </div>

</div>

@include('partials.footer')

</body>
</html>