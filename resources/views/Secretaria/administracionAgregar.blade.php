<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Administrador</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('fonts/remixicon/remixicon.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <h1>Agregar Administrador</h1>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>
        @endif

        <!-- FORM -->
        <form
            class="materia-form"
            method="POST"
            action="{{ route('administradores.store') }}">
            @csrf

            <div class="form-grid">

                <label>Apellido*</label>
                <input type="text" name="apellido" placeholder="Apellido">

                <label>Nombre*</label>
                <input type="text" name="nombre" placeholder="Nombre">

                <label>Tipo de documento*</label>
                <select name="id_tipo_dni">
                    <option disabled selected hidden>
                        Seleccionar tipo de documento
                    </option>

                    @foreach($tiposDni as $tipo)
                    <option value="{{ $tipo->id_tipo_dni }}">
                        {{ $tipo->descripcion }}
                    </option>
                    @endforeach
                </select>

                <label>Documento*</label>
                <input type="text" name="dni" placeholder="Documento">

                <label>CUIL/CUIT</label>
                <input type="text" name="cuil" placeholder="CUIL">

                <label>Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento">

                <label>Género</label>
                <select name="genero">
                    <option disabled selected hidden>Seleccionar</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="Otro">Otro</option>
                </select>

                <label>Nacionalidad</label>
                <select name="id_pais">
                    <option disabled selected hidden>
                        Seleccionar nacionalidad
                    </option>

                    @foreach($paises as $pais)
                        <option value="{{ $pais->id_pais }}">
                            {{ $pais->nombre_pais }}
                        </option>
                    @endforeach
                </select>

                <label>Localidad</label>
                <select name="id_localidad">
                    <option disabled selected hidden>
                        Seleccionar localidad
                    </option>

                    @foreach($localidades as $localidad)
                        <option value="{{ $localidad->id_localidad }}">
                            {{ $localidad->nombre_localidad }}
                        </option>
                    @endforeach
                </select>

                <label>Domicilio</label>
                <input type="text" name="domicilio" placeholder="Dirección">

                <label>Teléfono celular</label>
                <input type="text" name="telefonos[]" placeholder="Teléfono">

                <label>Email</label>
                <input type="email" name="email" placeholder="Email">

            </div>
            <div class="section-divider"></div>
            <div class="login-section">

                <h2>Inicio de Sesión</h2>

                <div class="form-grid">

                    <label>Nombre de usuario</label>
                    <input type="text" name="nombre_usuario" placeholder="Usuario">
                    <label>Clave</label>
                    <input type="password" name="password" placeholder="Clave">

                </div>
            </div>

            <!-- BOTONES -->
            <div class="form-actions">
                <button type="button" class="btn-secondary"
                        onclick="window.location.href='{{ route('administracionSec') }}'">
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
<script src="{{ asset('js/Secretaria/administradorCreate.js') }}"></script>
</body>
</html>
