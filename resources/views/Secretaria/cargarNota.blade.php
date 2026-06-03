<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/cargarNota.css') }}">
    {{-- <script id="materias-data" type="application/json">{{ $materias->toJson() }}</script> --}}
</head>

<body>
<button class="mobile-menu-btn" id="menu-btn">
    <i class="ri-menu-line"></i>
</button>
    @include('partials.sidebar')

    <div class="main">

        <h1>Cargar notas</h1>

        <div class="top-bar">
            <label>
                Cerrar cursada Automaticamente
                <input type="checkbox" checked>
            </label>
            <span class="help">
                Si esta activado, la cursada se cerrara automaticamente en base a la situacion de los dos cuatrimestres. Puede cambiar los datos de cierre manualmete.
            </span>
        </div>

        <div class="card-materia">
            <h2>Carrera</h2>
            <div class="input-box" id="materiaDropdown">
                <input
                    type="text"
                    id="materia-input"
                    placeholder="Materia"
                    autocomplete="off"
                >
                <div class="dropdown-menu" id="materia-list"></div>
            </div>
        </div>

        <div class="table-container">
            <table class="cursada-table">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>1° C</th>
                        <th>2° C</th>
                        <th>Rec./Prefinal</th>
                        <th>Cierre</th>
                    </tr>
                </thead>
                <tbody>

                    @php
                        $alumnos = ['Requena, Lucio', 'Miguel Rodriguez', 'Marcos, Lopez'];
                    @endphp

                    @foreach ($alumnos as $alumno)
                    <tr>
                        <td>{{ $alumno }}</td>

                        {{-- 1° Cuatrimestre --}}
                        <td class="nota-cell">
                            <div class="nota-wrapper dropdown">
                                <input type="text" class="nota-input" placeholder="Nota">
                                <button class="arrow-btn btn-dropdown">›</button>
                                <ul class="dropdown-menu-notas">
                                    <li><a class="dropdown-item opcion-nota" href="#">Promocionado</a></li>
                                    <li><a class="dropdown-item opcion-nota" href="#">Aprobado</a></li>
                                    <li><a class="dropdown-item opcion-nota" href="#">Desaprobado</a></li>
                                </ul>
                            </div>
                        </td>

                        {{-- 2° Cuatrimestre --}}
                        <td class="nota-cell">
                            <div class="nota-wrapper dropdown">
                                <input type="text" class="nota-input" placeholder="Nota">
                                <button class="arrow-btn btn-dropdown">›</button>
                                <ul class="dropdown-menu-notas">
                                    <li><a class="dropdown-item opcion-nota" href="#">Promocionado</a></li>
                                    <li><a class="dropdown-item opcion-nota" href="#">Aprobado</a></li>
                                    <li><a class="dropdown-item opcion-nota" href="#">Desaprobado</a></li>
                                </ul>
                            </div>
                        </td>

                        {{-- Rec./Prefinal --}}
                        <td class="nota-cell">
                            <div class="nota-wrapper dropdown">
                                <input type="text" class="nota-input" placeholder="Nota">
                                <button class="arrow-btn btn-dropdown">›</button>
                                <ul class="dropdown-menu-notas">
                                    <li><a class="dropdown-item opcion-nota" href="#">Promocionado</a></li>
                                    <li><a class="dropdown-item opcion-nota" href="#">Aprobado</a></li>
                                    <li><a class="dropdown-item opcion-nota" href="#">Desaprobado</a></li>
                                </ul>
                            </div>
                        </td>

                        {{-- Cierre --}}
                        <td class="nota-cell">
                            <div class="nota-wrapper dropdown">
                                <input type="text" class="nota-input" placeholder="Nota">
                                <button class="arrow-btn btn-dropdown">›</button>
                                <ul class="dropdown-menu-notas">
                                    <li><a class="dropdown-item opcion-nota" href="#">Promocionado</a></li>
                                    <li><a class="dropdown-item opcion-nota" href="#">Aprobado</a></li>
                                    <li><a class="dropdown-item opcion-nota" href="#">Desaprobado</a></li>
                                </ul>
                            </div>
                        </td>

                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div style="display:flex; justify-content:flex-start; gap:10px; margin-top:12px;">
            <button class="btn-secondary" id="btn-limpiar">
                <i class="ri-eraser-line"></i> Limpiar
            </button>
            <button class="btn-primary" id="btn-guardar">
                <i class="ri-save-line"></i> Guardar
            </button>
        </div>

    </div>

    @include('partials.footer')

    <script src="{{ asset('js/Secretaria/cargarNota.js') }}"></script>

</body>
</html>
