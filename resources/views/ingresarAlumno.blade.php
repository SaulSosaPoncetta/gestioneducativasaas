<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar Alumno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/homeSec.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ingModAlumno.css') }}">
</head>

<body>

@include('partials.sidebar')

<div class="main">

    <div class="form-container">
        <h1>Ingresar Alumno</h1>

        <form class="alumno-form">

            <div class="form-grid">

                <label>Apellido *</label>
                <input type="text" required>

                <label>Nombre *</label>
                <input type="text" required>

                <label>CUIL</label>
                <input type="text">

                <label>Fecha de nacimiento</label>
                <input type="date">

                <label>Nacionalidad</label>
                <select>
                    <option>Seleccionar</option>
                </select>

                <label>Localidad de nacimiento</label>
                <input type="text">

                <label>Domicilio</label>
                <input type="text">

                <label>Localidad del domicilio</label>
                <select>
                    <option>Seleccionar</option>
                </select>

                <label>Teléfono celular</label>
                <input type="text">

                <label>Teléfono secundario</label>
                <input type="text">

                <label>Contacto alternativo</label>
                <input type="text">

                <label>E-mail</label>
                <input type="email">

                <div class="form-actions">
                    <button type="button" class="btn-secondary">Volver</button>
                    <button class="btn-primary">Guardar</button>
                </div>
            </div>

        </form>
    </div>

</div>

@include('partials.footer')

</body>
</html>