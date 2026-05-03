<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pantalla Principal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/homeSec.css') }}">
</head>

<body>

    @include('partials.sidebar')

    <div class="main">

        <div class="grid">
            <div class="card"><i class="ri-book-line"></i><p>CARRERA</p></div>
            <div class="card"><i class="ri-book-open-line"></i><p>CURSADA</p></div>
            <div class="card"><i class="ri-award-line"></i><p>FINALES</p></div>
            <div class="card"><i class="ri-briefcase-line"></i><p>PROFESORES</p></div>
            <div class="card"><i class="ri-graduation-cap-line"></i><p>ALUMNOS</p></div>
            <div class="card"><i class="ri-file-line"></i><p>CONSTANCIAS</p></div>
            <div class="card"><i class="ri-calendar-line"></i><p>CICLO LECTIVO</p></div>
            <div class="card"><i class="ri-home-line"></i><p>MIS DATOS</p></div>
        </div>

    </div>

    @include('partials.footer')

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
