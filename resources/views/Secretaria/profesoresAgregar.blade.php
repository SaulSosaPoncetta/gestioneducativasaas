<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Profesor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filtro.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/materiaPerfil.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/profesoresAgregar.css') }}">
</head>

<body>
<button class="mobile-menu-btn" id="menu-btn">
    <i class="ri-menu-line"></i>
</button>
@include('partials.sidebar')

<div class="main">
    <div class="form-container">

        <!-- HEADER -->
        <div class="perfil-header">
            <h1>Agregar Profesor</h1>
        </div>

        <!-- FORM -->
        <form class="materia-form">

            <div class="form-grid">

                <label>Apellido</label>
                <input type="text" placeholder="Apellido">

                <label>Nombre</label>
                <input type="text" placeholder="Nombre">

                <label>Documento</label>
                <input type="text" placeholder="DNI">

                <label>Género</label>
                <select>
                    <option>Seleccionar</option>
                    <option>Masculino</option>
                    <option>Femenino</option>
                </select>

                <label>CUIL</label>
                <input type="text" placeholder="CUIL">

                <label>Fecha de nacimiento</label>
                <input type="date">

                <label>Nacionalidad</label>
                <input type="text" placeholder="Nacionalidad">

                <label>Localidad</label>
                <input type="text" placeholder="Localidad">

                <label>Domicilio</label>
                <input type="text" placeholder="Dirección">

                <label>Localidad domicilio</label>
                <input type="text" placeholder="Localidad">

                <label>Teléfono celular</label>
                <input type="text" placeholder="Teléfono">

                <label>Contacto alternativo</label>
                <input type="text" placeholder="Contacto alternativo">

                <label>Email</label>
                <input type="email" placeholder="Email">

            </div>
            <div class="section-divider"></div>
            <div class="login-section">

                <h2>Inicio de Sesión</h2>

                <div class="form-grid">

                    <label>Nombre de usuario</label>
                    <input type="text" placeholder="Usuario">
                    <label>Clave</label>
                    <input type="password" placeholder="Clave">

                </div>
            </div>

            <!-- BOTONES -->
            <div class="form-actions">
                <button type="button" class="btn-secondary"
                        onclick="window.location.href='{{ route('profesoresSec') }}'">
                    Volver
                </button>

                <button type="submit" class="btn-primary" id="btn-guardar">
                    <i class="ri-save-line"></i> Guardar
                </button>
            </div>

        </form>

    </div>
</div>

@include('partials.footer')
<script src="{{ asset('js/Secretaria/profesorCreate.js') }}"></script>
</body>
</html>
