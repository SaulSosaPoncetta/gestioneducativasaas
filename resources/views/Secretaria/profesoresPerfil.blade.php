<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil Profesor</title>
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
            <h1>Perfil Profesor</h1>
            <button class="btn-modificar" id="btn-modificar">
                <i class="ri-edit-line"></i> Modificar
            </button>
        </div>

        <!-- FORM -->
        <form class="materia-form">

            <div class="form-grid">

                <label>Apellido</label>
                <input type="text" placeholder="Apellido" disabled>

                <label>Nombre</label>
                <input type="text" placeholder="Nombre" disabled>

                <label>Documento</label>
                <input type="text" placeholder="DNI" disabled>

                <label>Género</label>
                <select disabled>
                    <option>Seleccionar</option>
                    <option>Masculino</option>
                    <option>Femenino</option>
                </select>

                <label>CUIL</label>
                <input type="text" placeholder="CUIL" disabled>

                <label>Fecha de nacimiento</label>
                <input type="date" disabled>

                <label>Nacionalidad</label>
                <input type="text" placeholder="Nacionalidad" disabled>

                <label>Localidad</label>
                <input type="text" placeholder="Localidad" disabled>

                <label>Domicilio</label>
                <input type="text" placeholder="Dirección" disabled>

                <label>Localidad domicilio</label>
                <input type="text" placeholder="Localidad" disabled>

                <label>Teléfono celular</label>
                <input type="text" placeholder="Teléfono" disabled>

                <label>Contacto alternativo</label>
                <input type="text" placeholder="Contacto alternativo" disabled>

                <label>Email</label>
                <input type="email" placeholder="Email" disabled>

            </div>
            <div class="section-divider"></div>
            <div class="login-section">

                <h2>Inicio de Sesión</h2>

                <div class="form-grid">

                    <label>Nombre de usuario</label>
                    <input type="text" placeholder="Usuario" disabled>
                    <label>Clave</label>
                    <input type="password" placeholder="Clave" disabled>

                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary" id="btn-guardar" disabled>
                    <i class="ri-save-line"></i> Guardar
                </button>
                <button type="button" class="btn-secondary" id="btn-cancelar" disabled>
                    Cancelar
                </button>
            </div>
        </form>

    </div>
</div>

@include('partials.footer')
    <script src="{{ asset('js/Secretaria/materiaPerfil.js') }}"></script>
</body>
</html>
