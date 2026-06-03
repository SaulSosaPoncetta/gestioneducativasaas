<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Alumno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

            <div class="perfil-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <button class="btn-secondary" type="button" onclick="window.location.href='{{ route('alumnos.index') }}'">
                        <i class="ri-arrow-left-line"></i> Volver
                    </button>
                    <h1>Alumno</h1>
                </div>
                <button class="btn-modificar" id="btn-modificar" type="button">
                    <i class="ri-edit-line"></i> Modificar
                </button>
            </div>

            {{-- Mensajes de éxito o error --}}
            @if (session('success'))
                <div class="alert alert-success" id="alert-success">
                    <i class="ri-checkbox-circle-line"></i> {{ session('success') }}
                </div>
            @endif

            <div id="alert-ajax" class="alert" style="display:none;"></div>

            <form class="alumno-form" id="form-alumno">
                @csrf
                @method('PUT')

                <div class="form-grid">

                    <label>Apellido</label>
                    <input
                        type="text"
                        name="apellido"
                        id="apellido"
                        value="{{ $estudiante->usuario->apellido }}"
                        disabled
                    >

                    <label>Nombre</label>
                    <input
                        type="text"
                        name="nombre"
                        id="nombre"
                        value="{{ $estudiante->usuario->nombre }}"
                        disabled
                    >

                    <label>DNI / CUIL</label>
                    <input
                        type="text"
                        id="dni_display"
                        value="{{ $dni ?? '—' }}"
                        disabled
                        {{-- El DNI no se edita desde este formulario --}}
                    >

                    <label>Localidad de domicilio</label>
                    <select name="id_localidad_domicilio" id="id_localidad_domicilio" disabled>
                        <option value="">Seleccionar</option>
                        @foreach ($localidades as $loc)
                            <option
                                value="{{ $loc->id_localidad }}"
                                {{ $estudiante->usuario->id_localidad == $loc->id_localidad ? 'selected' : '' }}
                            >
                                {{ $loc->nombre_localidad }}
                                @if ($loc->pais)
                                    ({{ $loc->pais->nombre_pais }})
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <label>Teléfono celular</label>
                    <input
                        type="text"
                        name="telefono_celular"
                        id="telefono_celular"
                        value="{{ $telefonos['celular'] ?? '' }}"
                        disabled
                    >

                    <label>Teléfono secundario</label>
                    <input
                        type="text"
                        name="telefono_secundario"
                        id="telefono_secundario"
                        value="{{ $telefonos['secundario'] ?? '' }}"
                        disabled
                    >

                    <label>E-mail</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ $estudiante->usuario->email }}"
                        disabled
                    >

                </div>

                <div class="section-divider"></div>

                <div class="login-section">
                    <h2>Inicio de Sesión</h2>
                    <div class="form-grid">

                        <label>Nombre de usuario</label>
                        <input
                            type="text"
                            name="nombre_usuario"
                            id="nombre_usuario"
                            value="{{ $estudiante->usuario->nombre_usuario }}"
                            disabled
                        >

                        <label>Nueva contraseña</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Dejar en blanco para no cambiar"
                            disabled
                        >

                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-primary" id="btn-guardar" disabled>
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

    <script>
    (function () {
        // ------------------------------------------------------------------
        // Referencias a elementos
        // ------------------------------------------------------------------
        const btnModificar = document.getElementById('btn-modificar');
        const btnGuardar   = document.getElementById('btn-guardar');
        const btnCancelar  = document.getElementById('btn-cancelar');
        const form         = document.getElementById('form-alumno');
        const alertAjax    = document.getElementById('alert-ajax');

        // Todos los inputs/selects editables del formulario
        const camposEditables = form.querySelectorAll(
            'input[name], select[name]'
        );

        // Guardar valores originales para poder cancelar
        let valoresOriginales = {};

        function guardarValoresOriginales() {
            camposEditables.forEach(function (campo) {
                valoresOriginales[campo.name] = campo.value;
            });
        }

        function restaurarValoresOriginales() {
            camposEditables.forEach(function (campo) {
                campo.value = valoresOriginales[campo.name] ?? '';
            });
        }

        function habilitarEdicion() {
            camposEditables.forEach(function (campo) {
                campo.disabled = false;
            });
            btnModificar.innerHTML = '<i class="ri-edit-line"></i> Editando...';
            btnModificar.classList.add('btn-editing');
            btnGuardar.disabled  = false;
            btnCancelar.disabled = false;
        }

        function deshabilitarEdicion() {
            camposEditables.forEach(function (campo) {
                campo.disabled = true;
            });
            btnModificar.innerHTML = '<i class="ri-edit-line"></i> Modificar';
            btnModificar.classList.remove('btn-editing');
            btnGuardar.disabled  = true;
            btnCancelar.disabled = true;
        }

        function mostrarAlerta(mensaje, tipo) {
            alertAjax.textContent = mensaje;
            alertAjax.className   = 'alert alert-' + tipo;
            alertAjax.style.display = 'block';
            // Ocultar después de 4 segundos
            setTimeout(function () {
                alertAjax.style.display = 'none';
            }, 4000);
        }

        // ------------------------------------------------------------------
        // Guardar valores al cargar la página
        // ------------------------------------------------------------------
        guardarValoresOriginales();

        // ------------------------------------------------------------------
        // Botón Modificar → habilita edición
        // ------------------------------------------------------------------
        btnModificar.addEventListener('click', function () {
            habilitarEdicion();
        });

        // ------------------------------------------------------------------
        // Botón Cancelar → restaura valores y deshabilita edición
        // ------------------------------------------------------------------
        btnCancelar.addEventListener('click', function () {
            restaurarValoresOriginales();
            deshabilitarEdicion();
            alertAjax.style.display = 'none';
        });

        // ------------------------------------------------------------------
        // Botón Guardar → envía los datos por AJAX (PUT)
        // ------------------------------------------------------------------
        btnGuardar.addEventListener('click', function () {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="ri-loader-4-line"></i> Guardando...';

            const formData = new FormData(form);
            // FormData no envía campos disabled, pero los nuestros ya están habilitados
            const datos = {};
            formData.forEach(function (valor, clave) {
                // Ignorar _token y _method
                if (clave !== '_token' && clave !== '_method') {
                    datos[clave] = valor;
                }
            });

            const idAlumno = {{ $estudiante->id_usuario }};
            const url      = '/alumnoPerfil/' + idAlumno;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(datos),
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { status: response.status, data: data };
                });
            })
            .then(function ({ status, data }) {
                if (data.success) {
                    guardarValoresOriginales(); // Actualizar valores originales con los nuevos
                    deshabilitarEdicion();
                    btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';
                    mostrarAlerta('✓ ' + data.message, 'success');
                } else {
                    // Mostrar errores de validación
                    let mensajeError = 'Errores de validación:\n';
                    if (data.errors) {
                        Object.values(data.errors).forEach(function (errs) {
                            mensajeError += '• ' + errs.join(', ') + '\n';
                        });
                    }
                    mostrarAlerta(mensajeError, 'error');
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';
                }
            })
            .catch(function (error) {
                mostrarAlerta('Error de conexión. Intentá de nuevo.', 'error');
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';
            });
        });

    })();
    </script>

</body>
</html>
