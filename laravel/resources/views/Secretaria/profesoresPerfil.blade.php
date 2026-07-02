<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil Profesor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('fonts/remixicon/remixicon.css') }}">

    @vite(['resources/css/app.css', 'resources/js/Filtro.js'])

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

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $dni = $docente->usuario->dnis->first();
            $telefono = $docente->usuario->telefonos->first();
        @endphp

        <!-- FORM -->
        <form id="form-profesor" method="POST" action="{{ route('profesores.update', $docente->usuario->id_usuario) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">

                <label>Apellido</label>
                <input type="text" name="apellido" value="{{ $docente->usuario->apellido }}" disabled>

                <label>Nombre</label>
                <input type="text" name="nombre" value="{{ $docente->usuario->nombre }}" disabled>

                <label>Tipo de documento</label>
                <select name="id_tipo_dni" disabled>
                    @foreach($tiposDni as $tipo)
                        <option
                            value="{{ $tipo->id_tipo_dni }}"
                            {{ ($dni?->id_tipo_dni == $tipo->id_tipo_dni) ? 'selected' : '' }}>
                            {{ $tipo->descripcion }}
                        </option>
                    @endforeach
                </select>

                <label>Documento</label>
                <input type="text" name="dni" value="{{ $dni?->dni ?? '' }}" disabled>

                <label>Género</label>
                <select name="genero" disabled>
                    <option value="Masculino" {{ $docente->usuario->genero == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                    <option value="Femenino"  {{ $docente->usuario->genero == 'Femenino'  ? 'selected' : '' }}>Femenino</option>
                    <option value="Otro"      {{ $docente->usuario->genero == 'Otro'      ? 'selected' : '' }}>Otro</option>
                </select>

                <label>CUIL/CUIT</label>
                <input type="text" name="cuil" value="{{ $dni?->cuil ?? '' }}" disabled>

                <label>Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento" value="{{ $docente->usuario->fecha_nacimiento ?? '' }}" disabled>

                <label>Nacionalidad</label>
                <select name="id_pais" disabled>
                    @foreach($paises as $pais)
                        <option
                            value="{{ $pais->id_pais }}"
                            {{ ($docente->usuario->pais->id_pais ?? null) == $pais->id_pais ? 'selected' : '' }}>
                            {{ $pais->nombre_pais }}
                        </option>
                    @endforeach
                </select>

                <label>Localidad</label>
                <select name="id_localidad" disabled>
                    @foreach($localidades as $localidad)
                        <option
                            value="{{ $localidad->id_localidad }}"
                            {{ ($docente->usuario->localidad->id_localidad ?? null) == $localidad->id_localidad ? 'selected' : '' }}>
                            {{ $localidad->nombre_localidad }}
                        </option>
                    @endforeach
                </select>

                <label>Domicilio</label>
                <input type="text" name="domicilio" value="{{ $docente->usuario->domicilio ?? '' }}" disabled>

                <label>Teléfono celular</label>
                <input type="text" name="telefono" value="{{ $telefono?->telefono ?? '' }}" disabled>

                <label>Email</label>
                <input type="email" name="email" value="{{ $docente->usuario->email }}" disabled>

                <label>Estado</label>
                <select name="estado" id="estado" disabled>
                    <option value="Activo"   {{ $docente->usuario->estado == 'Activo'   ? 'selected' : '' }}>Activo</option>
                    <option value="Inactivo" {{ $docente->usuario->estado == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>

            </div>

            <!-- Tabla de materias -->
            <div class="section-divider"></div>

            <div class="materias-section">
                <h2>Materias Asignadas</h2>

                @if($docente->materias->isEmpty())
                    <p class="sin-resultados">Sin materias asignadas.</p>
                @else
                    <table id="tablaMaterias" class="cursada-table">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Carrera</th>
                                <th>Tipo</th>
                                <th>Situación de Revista</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($docente->materias as $materia)
                                <tr>
                                    <td>{{ $materia->nombre }}</td>
                                    <td>{{ $materia->carrera->nombre ?? '—' }}</td>
                                    <td>{{ $materia->tipoMateria->descripcion_tipo_materia ?? '—' }}</td>
                                    <td>{{ $materia->pivot->situacion_revista ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Inicio de sesión -->
            <div class="section-divider"></div>

            <div class="login-section">
                <h2>Inicio de Sesión</h2>

                <div class="form-grid">
                    <label>Nombre de usuario</label>
                    <input type="text" name="nombre_usuario" value="{{ $docente->usuario->nombre_usuario }}" disabled>

                    <label>Clave</label>
                    <input type="password" value="********" disabled>
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
    const btnModificar    = document.getElementById('btn-modificar');
    const btnGuardar      = document.getElementById('btn-guardar');
    const btnCancelar     = document.getElementById('btn-cancelar');
    const camposEditables = document.querySelectorAll('#form-profesor input[name], #form-profesor select[name]');

    let valoresOriginales = {};

    function guardarOriginales() {
        camposEditables.forEach(c => valoresOriginales[c.name] = c.value);
    }

    function restaurarOriginales() {
        camposEditables.forEach(c => c.value = valoresOriginales[c.name] ?? '');
    }

    function habilitarEdicion() {
        camposEditables.forEach(c => c.disabled = false);
        btnModificar.innerHTML = '<i class="ri-edit-line"></i> Editando...';
        btnModificar.classList.add('btn-editing');
        btnGuardar.disabled  = false;
        btnCancelar.disabled = false;
    }

    function deshabilitarEdicion() {
        camposEditables.forEach(c => c.disabled = true);
        btnModificar.innerHTML = '<i class="ri-edit-line"></i> Modificar';
        btnModificar.classList.remove('btn-editing');
        btnGuardar.disabled  = true;
        btnCancelar.disabled = true;
    }

    guardarOriginales();

    btnModificar.addEventListener('click', habilitarEdicion);

    btnCancelar.addEventListener('click', function () {
        restaurarOriginales();
        deshabilitarEdicion();
    });

    btnGuardar.addEventListener('click', function () {
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="ri-loader-4-line"></i> Guardando...';

        const datos = {};
        camposEditables.forEach(c => {
            if (c.name && c.name !== '_token' && c.name !== '_method') {
                datos[c.name] = c.value;
            }
        });

        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('{{ route("profesores.update", $docente->usuario->id_usuario) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify(datos),
        })
        .then(r => r.json().then(data => ({ status: r.status, data })))
        .then(({ status, data }) => {
            if (data.success) {
                guardarOriginales();
                deshabilitarEdicion();
                btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';
            } else {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';
                if (data.errors) {
                    const mensajes = Object.values(data.errors).flat().join('\n');
                    alert('Errores de validación:\n' + mensajes);
                } else {
                    alert('Error al guardar. Revisá los datos.');
                }
            }
        })
        .catch(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="ri-save-line"></i> Guardar';
            alert('Error de conexión.');
        });
    });

    // Recargar al volver con la flecha del navegador
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) window.location.reload();
        const entries = performance.getEntriesByType('navigation');
        if (entries.length > 0 && entries[0].type === 'back_forward') window.location.reload();
    });
})();
</script>
</body>
</html>