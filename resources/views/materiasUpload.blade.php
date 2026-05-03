<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Materia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/homeSec.css') }}">
    <link rel="stylesheet" href="{{ asset('css/materiaUpload.css') }}">
</head>

<body>

    @include('partials.sidebar')

    <div class="main">

        <div class="form-container">
            <h1>Agregar Materia</h1>

            <form class="materia-form">

                <div class="form-grid">

                    <label>Nombre</label>
                    <input type="text">

                    <label>Plan de estudios</label>
                    <select>
                        <option>Seleccionar</option>
                    </select>

                    <label>Año curricular</label>
                    <select>
                        <option>Seleccionar</option>
                    </select>

                    <label>N° de orden dentro de año</label>
                    <select>
                        <option>Seleccionar</option>
                    </select>

                    <label>Carga horaria anual</label>
                    <input type="">

                </div>

                <div class="checkbox-group">

                    <label class="checkbox-row">
                        <span>TFO/EDI/UDI</span>
                        <input type="checkbox">
                    </label>

                    <label class="checkbox-row">
                        <span>Acreditable con final</span>
                        <input type="checkbox">
                    </label>

                    <label class="checkbox-row">
                        <span>Promocionable</span>
                        <input type="checkbox">
                    </label>

                    <label class="checkbox-row">
                        <span>Permite libres</span>
                        <input type="checkbox">
                    </label>

                    <label class="checkbox-row">
                        <span>Optativa</span>
                        <input type="checkbox">
                    </label>

                </div>

                <div class="form-actions">
                    <button class="btn-primary">Guardar</button>
                    <button type="button" class="btn-secondary">Volver</button>
                </div>

            </form>
        </div>

    </div>

    @include('partials.footer')

</body>
</html>