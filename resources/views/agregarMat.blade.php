<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/agregarMat.css') }}">
</head>

<body>

    <div class="sidebar">

        <div>
            <div class="top-icons">
                {{-- <i class="ri-menu-line" id="menu-btn"></i> --}}
                <i class="ri-notification-3-line"></i>
                <i class="ri-logout-box-r-line"></i>
            </div>

            <div class="profile">
                <img src="https://via.placeholder.com/130">
            </div>

            <div class="menu">
                <a href="#"><i class="ri-book-line"></i> Carrera</a>
                <a href="#"><i class="ri-calendar-line"></i> Ciclo Lectivo</a>
                <a href="cursadaSec"><i class="ri-book-open-line"></i> Cursada</a>
                <a href="#"><i class="ri-award-line"></i> Finales</a>
                <a href="#"><i class="ri-graduation-cap-line"></i> Alumnos</a>
                <a href="#"><i class="ri-briefcase-line"></i> Profesores</a>
                <a href="#"><i class="ri-file-line"></i> Constancias</a>
                <a href="#"><i class="ri-home-line"></i> Mis datos</a>
            </div>
        </div>

        <div class="sidebar-footer">
            <div class="status">
                <span>Estado del Servidor</span>
                <div class="dot"></div>
            </div>
        </div>

    </div>

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

        <div class="card">
            <h2>Carrera</h2>

        <div class="input-box">
            <input type="text" placeholder="Materia"><i class="ri-arrow-right-s-line"></i>
        </div>
        </div>

        <div class="table-container">

            <table>
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
                    <tr>
                        <td>Requena, Lucio</td>

                        <td class="nota-cell">
                            <div class="nota-wrapper">
                                <input type="text" class="nota-input" placeholder="Nota">
                                <button class="arrow-btn">›</button>
                            </div>
                        </td>

                        <td class="nota-cell">
                            <div class="nota-wrapper">
                                <input type="text" class="nota-input" placeholder="Nota">
                                <button class="arrow-btn">›</button>
                            </div>
                        </td>
                        <td></td>

                        <td class="nota-cell">
                            <div class="nota-wrapper">
                                <input type="text" class="nota-input" placeholder="Nota">
                                <button class="arrow-btn">›</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

    </div>

    <footer>
        <pre>@2027 - SGI</pre>
    </footer>

    <div class="overlay" id="overlay"></div>

        <script>
            const menuBtn = document.getElementById('menu-btn');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('overlay');

            menuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        </script>

</body>
</html>
