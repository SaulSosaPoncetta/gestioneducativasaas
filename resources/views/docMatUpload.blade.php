<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Profesor a Materia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/homeSec.css') }}">
    <link rel="stylesheet" href="{{ asset('css/docMatUpload.css') }}">
</head>

<body>

@include('partials.sidebar')

<div class="main">

    <div class="form-container">
        <h1>Agregar Profesor a Materia</h1>

        <form class="materia-form">

            <div class="form-grid">

                <label>Cursada</label>
                <select>
                    <option>Seleccionar</option>
                </select>

                <label>Profesor</label>
                <select>
                    <option>Seleccionar</option>
                </select>

                <label>División</label>
                <select>
                    <option>Seleccionar</option>
                </select>

                <label>Situación de revista</label>
                <select>
                    <option>Seleccionar</option>
                </select>

                <label>Fecha inicio</label>
                <input type="date">

                <label>Fecha fin</label>
                <input type="date">

                <label>Horas semanales</label>
                <input type="number" placeholder="Ej: 4">

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