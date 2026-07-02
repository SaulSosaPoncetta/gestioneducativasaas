{{-- analiticoAlumno.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Analítico del Alumno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('fonts/remixicon/remixicon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filtro.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/analitico.css') }}">
</head>

<body>
<button class="mobile-menu-btn" id="menu-btn">
    <i class="ri-menu-line"></i>
</button>

@include('partials.sidebar')

<div class="main">

    {{-- TOP --}}
    <div class="top-bar">
        <h1>Analítico del Alumno</h1>
    </div>

    {{-- SELECT DE CARRERA --}}
    <div class="career-select-card">

        <label for="sel-carrera">Carrera</label>

        <select id="sel-carrera">
            <option value="">Seleccionar carrera</option>

            {{-- Backend --}}
            {{--
            @foreach($carreras as $carrera)
                <option value="{{ $carrera->id }}">
                    {{ $carrera->nombre }}
                </option>
            @endforeach
            --}}

            {{-- Demo --}}
            <option value="1">Analista de Sistemas Res. 6790/19</option>
            <option value="2">Tecnicatura en Programación</option>
        </select>

    </div>

    {{-- PLACEHOLDER SIN CARRERA --}}
    <div class="empty-state" id="empty-career">
        <i class="ri-graduation-cap-line"></i>
        <p>Seleccioná una carrera para visualizar el analítico.</p>
    </div>

    {{-- CONTENIDO ANALÍTICO --}}
    <div id="analytic-content" style="display: none;">

        {{-- HEADER ANALÍTICO --}}
        <div class="analytic-header">

            <div class="analytic-info">
                <h2 id="career-title">
                    Analista de Sistemas Res. 6790/19
                </h2>

                <div class="analytic-stats">

                    <div class="stat-box">
                        <span class="stat-label">Espacios acreditados:</span>
                        <span class="stat-number" id="approved-count">8</span>
                    </div>

                    <div class="progress-box">

                        <div class="progress-label">
                            Porcentaje de la carrera:
                        </div>

                        <div class="progress-circle">
                            <svg width="120" height="120">

                                <circle
                                    class="bg"
                                    cx="60"
                                    cy="60"
                                    r="48"
                                ></circle>

                                <circle
                                    class="progress"
                                    cx="60"
                                    cy="60"
                                    r="48"
                                    id="progress-circle"
                                ></circle>

                            </svg>

                            <div class="progress-text" id="progress-text">
                                38%
                            </div>

                        </div>

                    </div>

                </div>
            </div>

            {{-- BOTÓN IMPRIMIR --}}
            <button class="print-btn" title="Imprimir">
                <i class="ri-printer-line"></i>
            </button>

        </div>

        {{-- PLACEHOLDER SIN DATOS --}}
        <div class="table-placeholder" id="no-data-placeholder" style="display: none;">
            <i class="ri-file-search-line"></i>
            <p>No hay materias cursadas para esta carrera.</p>
        </div>

        {{-- TABLAS --}}
        <div id="tables-content">

            {{-- 1ER AÑO --}}
            <div class="year-block">

                <table class="analytic-table">

                    <thead>
                        <tr>
                            <th>
                                Espacio Curricular
                                <span class="year-title">1° AÑO</span>
                            </th>

                            <th>Cursada</th>

                            <th>Acreditación / Final</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>Inglés I</td>

                            <td>
                                <span class="status approved">
                                    Aprobada en 2024
                                </span>

                                - Nota: 9,00
                            </td>

                            <td>
                                <span class="status accredited">
                                    Acreditado el 20/11/2024
                                </span>

                                - Nota: 8,00
                            </td>
                        </tr>

                        <tr>
                            <td>Análisis Matemático I</td>

                            <td>
                                <span class="status approved">
                                    Aprobada en 2024
                                </span>

                                - Nota: 8,50
                            </td>

                            <td>
                                <span class="status accredited">
                                    Acreditado el 17/02/2025
                                </span>

                                - Nota: 6,00
                            </td>
                        </tr>

                        <tr>
                            <td>Álgebra</td>

                            <td>
                                <span class="status approved">
                                    Aprobada en 2024
                                </span>

                                - Nota: 7,00
                            </td>

                            <td>
                                <span class="status accredited">
                                    Acreditado el 06/12/2024
                                </span>

                                - Nota: 9,00
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

            {{-- 2DO AÑO --}}
            <div class="year-block">

                <table class="analytic-table">

                    <thead>
                        <tr>
                            <th>
                                Espacio Curricular
                                <span class="year-title">2° AÑO</span>
                            </th>

                            <th>Cursada</th>

                            <th>Acreditación / Final</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>Sistemas Operativos</td>

                            <td>
                                <span class="status approved">
                                    Aprobada en 2025
                                </span>

                                - Nota: 8,00
                            </td>

                            <td>
                                <span class="status pending">
                                    Pendiente
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td>Base de Datos</td>

                            <td>
                                <span class="status approved">
                                    Aprobada en 2025
                                </span>

                                - Nota: 10,00
                            </td>

                            <td>
                                <span class="status accredited">
                                    Acreditado el 10/03/2026
                                </span>

                                - Nota: 9,00
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@include('partials.footer')

<div class="overlay" id="overlay"></div>

<script src="{{ asset('js/Secretaria/analitico.js') }}" defer></script>

</body>
</html>
