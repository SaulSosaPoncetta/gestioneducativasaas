<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cursadaSec.css') }}">
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

    <h1>Buscar por carrera</h1>

    <div class="card">

        <h2>Carrera</h2>

        <div class="input-group">
            <div class="input-box">
                <input type="text" placeholder="Nombre">
                <i class="ri-arrow-right-s-line"></i>
            </div>
        </div>

        <div class="input-group">
            <div class="input-box">
                <input type="text" placeholder="Plan de estudio">
                <i class="ri-arrow-right-s-line"></i>
            </div>
        </div>

    </div>

        <div class="buttons">
            <button class="btn btn-outline">Limpiar</button>
            <button class="btn btn-primary" onclick="window.location='/cursadaFilt'">Filtrar</button>
        </div>

    </div>

    <footer>
        <pre>@2027 - SGI</pre>
    </footer>

    <div class="overlay" id="overlay"></div>

        <script>
            // const menuBtn = document.getElementById('menu-btn');
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
