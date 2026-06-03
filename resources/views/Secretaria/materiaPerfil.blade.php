<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil Materia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filtro.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/materiaPerfil.css') }}">
</head>

<body>
    <button class="mobile-menu-btn" id="menu-btn">
    <i class="ri-menu-line"></i>
    </button>
    @include('partials.sidebar')

    <div class="main">
        <div class="form-container">

            <div class="perfil-header">
                <h1>0804 - Algebra</h1>
                <button class="btn-modificar" id="btn-modificar">
                    <i class="ri-edit-line"></i> Modificar
                </button>
            </div>

            <form class="materia-form">

                <div class="form-grid">

                    <label>Nombre</label>
                    <input type="text" value="Algebra" disabled>

                    <label>Profesor</label>
                    <div class="dropdown" id="profesorDropdown">
                        <input
                            type="text"
                            id="profesor-input"
                            value="García, Roberto"
                            placeholder="Buscar profesor..."
                            autocomplete="off"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            disabled
                        >
                        <ul class="dropdown-menu w-100" id="profesor-list" style="max-height:220px; overflow-y:auto;"></ul>
                    </div>

                    <label>Plan de estudios</label>
                    <select disabled>
                        <option selected>Analista de Sistemas Res. 6790/19</option>
                    </select>

                    <label>Año curricular</label>
                    <select disabled>
                        <option selected>1° año</option>
                    </select>

                    <label>N° de orden dentro de año</label>
                    <select disabled>
                        <option selected>4</option>
                    </select>

                    <label>Carga horaria anual</label>
                    <input type="number" value="64" disabled>

                </div>

                <div class="checkbox-group">

                    <label class="checkbox-row">
                        <span>TFO/EDI/UDI</span>
                        <input type="checkbox" checked disabled>
                    </label>

                    <label class="checkbox-row">
                        <span>Acreditable con final</span>
                        <input type="checkbox" checked disabled>
                    </label>

                    <label class="checkbox-row">
                        <span>Promocionable</span>
                        <input type="checkbox" checked disabled>
                    </label>

                    <label class="checkbox-row">
                        <span>Permite libres</span>
                        <input type="checkbox" checked disabled>
                    </label>

                    <label class="checkbox-row">
                        <span>Optativa</span>
                        <input type="checkbox" checked disabled>
                    </label>

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

    <div class="overlay" id="overlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/Secretaria/materiaPerfil.js') }}"></script>

</body>
</html>
