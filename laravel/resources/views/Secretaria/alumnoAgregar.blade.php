<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar Alumno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('fonts/remixicon/remixicon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filtro.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/materiaPerfil.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/profesoresAgregar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/ingModAlumno.css') }}">
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
                <h1>Ingresar Alumno</h1>
            </div>

            <div id="alert-ajax" class="alert" style="display:none;"></div>

            <form class="alumno-form" id="form-alumno">
                @csrf

                <div class="form-grid">

                    <label>Apellido *</label>
                    <input type="text" name="apellido" id="apellido" placeholder="Apellido" required>

                    <label>Nombre *</label>
                    <input type="text" name="nombre" id="nombre" placeholder="Nombre" required>

                    <label>Tipo de documento *</label>
                    <select name="id_tipo_dni" id="id_tipo_dni" required>
                        <option disabled selected hidden>Seleccionar tipo de documento</option>
                        @foreach ($tiposDni as $tipo)
                            <option value="{{ $tipo->id_tipo_dni }}">{{ $tipo->descripcion }}</option>
                        @endforeach
                    </select>

                    <label>Documento *</label>
                    <input type="text" name="dni" id="dni" placeholder="Número de documento" required>

                    <label>CUIL/CUIT</label>
                    <input type="text" name="cuil" id="cuil" placeholder="CUIL/CUIT">

                    <label>Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento">

                    <label>Género</label>
                    <select name="genero" id="genero">
                        <option disabled selected hidden>Seleccionar</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>

                    <label>Nacionalidad</label>
                    <select name="id_pais" id="id_pais">
                        <option disabled selected hidden>Seleccionar nacionalidad</option>
                        @foreach ($paises as $pais)
                            <option value="{{ $pais->id_pais }}">{{ $pais->nombre_pais }}</option>
                        @endforeach
                    </select>

                    <label>Localidad *</label>
                    <select name="id_localidad" id="id_localidad" required>
                        <option disabled selected hidden>Seleccionar localidad</option>
                        @foreach ($localidades as $localidad)
                            <option value="{{ $localidad->id_localidad }}">
                                {{ $localidad->nombre_localidad }}
                                @if($localidad->pais)
                                    ({{ $localidad->pais->nombre_pais }})
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <label>Domicilio</label>
                    <input type="text" name="domicilio" id="domicilio" placeholder="Dirección">

                    <label>Teléfono celular</label>
                    <input type="text" name="telefono_celular" id="telefono_celular" placeholder="Teléfono celular">

                    <label>Teléfono secundario</label>
                    <input type="text" name="telefono_secundario" id="telefono_secundario" placeholder="Teléfono secundario">

                    <label>E-mail *</label>
                    <input type="email" name="email" id="email" placeholder="Email" required>

                </div>

                <div class="section-divider"></div>

                <div class="login-section">
                    <h2>Inicio de Sesión</h2>
                    <div class="form-grid">

                        <label>Nombre de usuario *</label>
                        <input type="text" name="nombre_usuario" id="nombre_usuario" placeholder="Usuario" required>

                        <label>Contraseña *</label>
                        <input type="password" name="password" id="password" placeholder="Contraseña (mínimo 8 caracteres)" required>

                    </div>
                </div>

                <!-- BOTONES -->
                <div class="form-actions">
                    <button type="button" class="btn-secondary"
                            onclick="window.location.href='{{ route('alumnos.index') }}'">
                        Volver
                    </button>
                    <button type="button" class="btn-primary" id="btn-guardar">
                        <i class="ri-save-line"></i> Guardar
                    </button>
                </div>

            </form>

        </div>
    </div>

    @include('partials.footer')

    <script>
    (function () {
        const btnGuardar = document.getElementById('btn-guardar');
        const form       = document.getElementById('form-alumno');
        const alertAjax  = document.getElementById('alert-ajax');
        const csrfToken  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function mostrarAlerta(mensaje, tipo) {
            alertAjax.textContent   = mensaje;
            alertAjax.className     = 'alert alert-' + tipo;
            alertAjax.style.display = 'block';
            alertAjax.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            if (tipo === 'success') {
                setTimeout(function () {
                    alertAjax.style.display = 'none';
                }, 5000);
            }
        }

        btnGuardar.addEventListener('click', function () {
            // Validación nativa HTML5
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            btnGuardar.disabled  = true;
            btnGuardar.innerHTML = '<i class="ri-loader-4-line"></i> Guardando...';

            // Recolectar datos del formulario
            const formData = new FormData(form);
            const datos    = {};
            formData.forEach(function (valor, clave) {
                if (clave !== '_token') {
                    datos[clave] = valor;
                }
            });

            fetch('{{ route('alumnos.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept':       'application/json',
                },
                body: JSON.stringify(datos),
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { status: response.status, data: data };
                });
            })
            .then(function ({ status, data }) {
                btnGuardar.disabled  = false;
                btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';

                if (data.success) {
                    mostrarAlerta('✓ ' + data.message, 'success');
                    // Redirigir a la lista de alumnos después de 1.5 seg
                    setTimeout(function () {
                        window.location.href = '{{ route('alumnos.index') }}';
                    }, 1500);
                } else {
                    let mensajeError = 'Errores de validación: ';
                    if (data.errors) {
                        mensajeError += Object.values(data.errors).flat().join(' | ');
                    } else if (data.message) {
                        mensajeError = data.message;
                    }
                    mostrarAlerta(mensajeError, 'error');
                }
            })
            .catch(function () {
                btnGuardar.disabled  = false;
                btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';
                mostrarAlerta('Error de conexión. Intentá de nuevo.', 'error');
            });
        });
    })();
    </script>

</body>
</html>
