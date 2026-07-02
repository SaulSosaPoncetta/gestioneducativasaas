<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Alumno</title>
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
                <div style="display:flex; align-items:center; gap:12px;">
                    <button class="btn-secondary" onclick="window.history.back()" type="button">
                        ← Volver
                    </button>
                    <h1>Alumno</h1>
                </div>
                <button class="btn-modificar" id="btn-modificar" type="button">
                    <i class="ri-edit-line"></i> Modificar
                </button>
            </div>

            {{-- Mensajes flash de sesión --}}
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

                    <label>Tipo de documento</label>
                    <select name="id_tipo_dni" id="id_tipo_dni" disabled>
                        <option value="">Seleccionar</option>
                        @foreach ($tiposDni as $tipo)
                            <option
                                value="{{ $tipo->id_tipo_dni }}"
                                {{ $idTipoDni == $tipo->id_tipo_dni ? 'selected' : '' }}
                            >
                                {{ $tipo->descripcion }}
                            </option>
                        @endforeach
                    </select>

                    <label>DNI / CUIL</label>
                    <input
                        type="text"
                        name="dni"
                        id="dni"
                        value="{{ $dniNumero !== '—' ? $dniNumero : '' }}"
                        placeholder="Número de documento"
                        disabled
                    >

                    <label>CUIL/CUIT</label>
                    <input
                        type="text"
                        name="cuil"
                        id="cuil"
                        value="{{ $dniCuil }}"
                        placeholder="CUIL/CUIT"
                        disabled
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
                        value="{{ $telefonos['celular'] }}"
                        disabled
                    >

                    <label>Teléfono secundario</label>
                    <input
                        type="text"
                        name="telefono_secundario"
                        id="telefono_secundario"
                        value="{{ $telefonos['secundario'] }}"
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

                    <label>Estado</label>
                    <select name="estado" id="estado" disabled>
                        <option value="Activo"   {{ $estudiante->usuario->estado === 'Activo'   ? 'selected' : '' }}>Activo</option>
                        <option value="Inactivo" {{ $estudiante->usuario->estado === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>

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
                    <button type="button" class="btn-primary" id="btn-guardar" style="display:none;">
                        <i class="ri-save-line"></i> Guardar
                    </button>
                    <button type="button" class="btn-secondary" id="btn-cancelar" style="display:none;">
                        Cancelar
                    </button>
                </div>

            </form>
        </div>
    </div>

    @include('partials.footer')

    <script>
    (function () {
        const btnModificar = document.getElementById('btn-modificar');
        const btnGuardar   = document.getElementById('btn-guardar');
        const btnCancelar  = document.getElementById('btn-cancelar');
        const form         = document.getElementById('form-alumno');
        const alertAjax    = document.getElementById('alert-ajax');

        // Todos los inputs/selects con name (excluir _token y _method)
        const camposEditables = Array.from(
            form.querySelectorAll('input[name], select[name]')
        ).filter(el => el.name !== '_token' && el.name !== '_method');

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
            btnGuardar.style.display  = '';
            btnCancelar.style.display = '';
        }

        function deshabilitarEdicion() {
            camposEditables.forEach(function (campo) {
                campo.disabled = true;
            });
            btnModificar.innerHTML = '<i class="ri-edit-line"></i> Modificar';
            btnModificar.classList.remove('btn-editing');
            btnGuardar.style.display  = 'none';
            btnCancelar.style.display = 'none';
        }

        function mostrarAlerta(mensaje, tipo) {
            alertAjax.textContent     = mensaje;
            alertAjax.className       = 'alert alert-' + tipo;
            alertAjax.style.display   = 'block';
            setTimeout(function () {
                alertAjax.style.display = 'none';
            }, 5000);
        }

        // Guardar valores al cargar la página
        guardarValoresOriginales();

        // Botón Modificar → habilita edición
        btnModificar.addEventListener('click', function () {
            habilitarEdicion();
        });

        // Botón Cancelar → restaura y deshabilita
        btnCancelar.addEventListener('click', function () {
            restaurarValoresOriginales();
            deshabilitarEdicion();
            alertAjax.style.display = 'none';
        });

        // Botón Guardar → AJAX PUT
        btnGuardar.addEventListener('click', function () {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="ri-loader-4-line"></i> Guardando...';

            const datos = {};
            camposEditables.forEach(function (campo) {
                datos[campo.name] = campo.value;
            });

            const idAlumno  = {{ $estudiante->id_usuario }};
            const url       = '/alumnoPerfil/' + idAlumno;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(url, {
                method: 'PUT',
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
                    guardarValoresOriginales();
                    deshabilitarEdicion();
                    mostrarAlerta('✓ ' + data.message, 'success');
                } else {
                    let mensajeError = 'Errores de validación: ';
                    if (data.errors) {
                        mensajeError += Object.values(data.errors).flat().join(' | ');
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

        // Recargar la página al volver para atrás (para reflejar cambios)
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
            const perfEntries = performance.getEntriesByType('navigation');
            if (perfEntries.length > 0 && perfEntries[0].type === 'back_forward') {
                window.location.reload();
            }
        });

    })();
    </script>

</body>
</html>
