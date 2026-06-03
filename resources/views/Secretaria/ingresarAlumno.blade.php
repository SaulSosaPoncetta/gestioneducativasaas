<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar Alumno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filtro.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/ingModAlumno.css') }}">
</head>

<body>
    <button class="mobile-menu-btn" id="menu-btn">
    <i class="ri-menu-line"></i>
    </button>
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
                    <button type="button" class="btn-secondary"onclick="window.history.back()">Volver</button>
                    <button class="btn-primary">Guardar</button>
                </div>

            </div>

        </form>
    </div>

</div>

@include('partials.footer')

<div class="overlay" id="overlay"></div>

<script>
    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('overlay');

    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
</script>

</body>
</html>
