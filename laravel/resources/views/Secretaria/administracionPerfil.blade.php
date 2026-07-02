<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Administrador</title>
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

            <div class="perfil-header">
                <div>
                    <h1>Administrador</h1>
                </div>
                <button class="btn-modificar" id="btn-modificar" type="button">
                    <i class="ri-edit-line"></i> Modificar
                </button>
            </div>

            @if (session('success'))
                <div class="alert alert-success" id="alert-success">
                    <i class="ri-checkbox-circle-line"></i> {{ session('success') }}
                </div>
            @endif

            <div id="alert-ajax" class="alert" style="display:none;"></div>

            <form class="alumno-form" id="form-administrador">
                @csrf
                @method('PUT')

                <div class="form-grid">

                    <label>Apellido</label>
                    <input
                        type="text"
                        name="apellido"
                        id="apellido"
                        value="{{ $administrador->usuario->apellido }}"
                        disabled
                    >

                    <label>Nombre</label>
                    <input
                        type="text"
                        name="nombre"
                        id="nombre"
                        value="{{ $administrador->usuario->nombre }}"
                        disabled
                    >
                    <label>Fecha de nacimiento</label>
                    <input
                        type="date"
                        name="fecha_nacimiento"
                        id="fecha_nacimiento"
                        value="{{ $administrador->usuario->fecha_nacimiento ? \Carbon\Carbon::parse($administrador->usuario->fecha_nacimiento)->format('Y-m-d') : '' }}"
                        disabled
                    >

                    @php $primerDni = $dni->first(); @endphp

                    <label>Tipo de documento</label>
                    <select name="id_tipo_dni" id="id_tipo_dni" disabled>
                        @foreach($tiposDni as $tipo)
                            <option

                                value="{{ $tipo->id_tipo_dni }}"
                                {{ ($primerDni?->id_tipo_dni == $tipo->id_tipo_dni) ? 'selected' : '' }}>
                                {{ $tipo->descripcion }}

                            </option>
                        @endforeach
                    </select>

                    <label>Documento</label>
                    <input type="text" name="dni" value="{{ $primerDni?->dni ?? '' }}" disabled>

                    <label>CUIL/CUIT</label>
                    <input
                        type="text"
                        id="cuil_display"
                        name="cuil"
                        value="{{ $primerDni?->cuil ?? '' }}"
                        disabled
                    >


                    <input type="hidden" name="dni"      value="{{ $primerDni?->dni ?? '' }}">
                    <input type="hidden" name="tipo_dni" value="{{ $primerDni?->tipo_dni ?? 'DNI' }}">

                    <label>Nacionalidad</label>
                    <select name="id_pais" id="id_pais" disabled>
                        <option value="">Seleccionar</option>
                        @foreach ($paises as $pais)
                            <option
                                value="{{ $pais->id_pais }}"
                                {{ $administrador->usuario->id_pais == $pais->id_pais ? 'selected' : '' }}
                            >
                                {{ $pais->nombre_pais }}
                            </option>
                        @endforeach
                    </select>

                    <label>Localidad</label>
                    <select name="id_localidad" id="id_localidad" disabled>
                        <option value="">Seleccionar</option>
                        @foreach ($localidades as $loc)
                            <option
                                value="{{ $loc->id_localidad }}"
                                {{ $administrador->usuario->id_localidad == $loc->id_localidad ? 'selected' : '' }}
                            >
                                {{ $loc->nombre_localidad }}
                                @if ($loc->pais)
                                    ({{ $loc->pais->nombre_pais }})
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <label>Domicilio</label>
                    <input
                        type="text"
                        name="domicilio"
                        id="domicilio"
                        value="{{ $administrador->usuario->domicilio ?? '' }}"
                        disabled
                    >

                    <label>Teléfono celular</label>
                    <input
                        type="text"
                        name="telefono_celular"
                        id="telefono_celular"
                        value="{{ $telefonos->get(0)->telefono ?? '' }}"
                        disabled
                    >

                    <label>E-mail</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ $administrador->usuario->email }}"
                        disabled
                    >
                    <label>Género</label>
                    <select name="genero" id="genero" disabled>
                    <option value="M"    {{ $administrador->usuario->genero === 'M'    ? 'selected' : '' }}>Masculino</option>
                    <option value="F"    {{ $administrador->usuario->genero === 'F'    ? 'selected' : '' }}>Femenino</option>
                    <option value="Otro" {{ $administrador->usuario->genero === 'Otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                    <label>Estado</label>
                    <select name="estado" id="estado_display" disabled>
                        <option value="Activo"   {{ $administrador->usuario->estado === 'Activo'   ? 'selected' : '' }}>Activo</option>
                        <option value="Inactivo" {{ $administrador->usuario->estado === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
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
                            value="{{ $administrador->usuario->nombre_usuario }}"
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
            const btnModificar = document.getElementById('btn-modificar');
            const btnGuardar   = document.getElementById('btn-guardar');
            const btnCancelar  = document.getElementById('btn-cancelar');
            const form         = document.getElementById('form-administrador');
            const alertAjax    = document.getElementById('alert-ajax');

            const camposEditables = form.querySelectorAll('input, select');

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
                alertAjax.textContent   = mensaje;
                alertAjax.className     = 'alert alert-' + tipo;
                alertAjax.style.display = 'block';
                setTimeout(function () {
                    alertAjax.style.display = 'none';
                }, 4000);
            }

            guardarValoresOriginales();

            btnModificar.addEventListener('click', function () {
                habilitarEdicion();
            });

            btnCancelar.addEventListener('click', function () {
                restaurarValoresOriginales();
                deshabilitarEdicion();
                alertAjax.style.display = 'none';
            });

            btnGuardar.addEventListener('click', function () {
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = '<i class="ri-loader-4-line"></i> Guardando...';

                // Tomar valores directo del DOM porque disabled no se incluye en FormData
                const datos = {
                    apellido:         document.querySelector('[name="apellido"]').value,
                    nombre:           document.querySelector('[name="nombre"]').value,
                    fecha_nacimiento: document.querySelector('[name="fecha_nacimiento"]').value,
                    id_tipo_dni:      document.querySelector('[name="id_tipo_dni"]').value,
                    dni:              document.querySelector('input[name="dni"]').value,
                    cuil:             document.querySelector('[name="cuil"]').value,
                    id_pais:          document.querySelector('[name="id_pais"]').value,
                    id_localidad:     document.querySelector('[name="id_localidad"]').value,
                    domicilio:        document.querySelector('[name="domicilio"]').value,
                    email:            document.querySelector('[name="email"]').value,
                    genero:           document.querySelector('[name="genero"]').value,
                    estado:           document.querySelector('[name="estado"]').value,
                    nombre_usuario:   document.querySelector('[name="nombre_usuario"]').value,
                    password:         document.querySelector('[name="password"]').value,
                    telefonos:        [],
                };

                const tel = document.querySelector('[name="telefono_celular"]').value;
                if (tel) datos.telefonos.push(tel);

                const idAdministrador = {{ $administrador->id_usuario }};
                const url = '/administrador/' + idAdministrador;
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
                    if (!response.ok) {
                        return response.text().then(function (text) {
                            mostrarAlerta('Error HTTP ' + response.status + ': ' + text.substring(0, 300), 'error');
                            btnGuardar.disabled = false;
                            btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';
                            throw new Error('HTTP ' + response.status);
                        });
                    }
                    return response.json().then(function (data) {
                        return { status: response.status, data: data };
                    });
                })
                .then(function ({ status, data }) {
                    if (data.success) {
                        guardarValoresOriginales();
                        deshabilitarEdicion();
                        btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';
                        mostrarAlerta('✓ ' + data.message, 'success');
                    } else {
                        let mensajeError = 'Errores de validación: ';
                        if (data.errors) {
                            Object.entries(data.errors).forEach(function ([campo, errs]) {
                                mensajeError += campo + ': ' + errs.join(', ') + ' | ';
                            });
                        }
                        mostrarAlerta(mensajeError, 'error');
                        btnGuardar.disabled = false;
                        btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';
                    }
                })
                .catch(function (err) {
                    mostrarAlerta('Error: ' + err.message, 'error');
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';
                });
            });
        })();
    </script>

</body>
</html>
