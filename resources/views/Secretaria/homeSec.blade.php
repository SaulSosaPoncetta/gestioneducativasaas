<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pantalla Principal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/homeSec.css') }}">
</head>

<body>
    <button class="mobile-menu-btn" id="menu-btn">
    <i class="ri-menu-line"></i>
    </button>
    @include('partials.sidebar')

    <div class="main">

        <div class="grid">
            <div class="card"><i class="ri-book-line"></i><p>CARRERA</p></div>
            <a href="{{ route('cursada') }}" class="card"><i class="ri-book-open-line"></i><p>CURSADA</p></a>
            <div class="card"><i class="ri-award-line"></i><p>FINALES</p></div>
            <div class="card"><i class="ri-briefcase-line"></i><p>PROFESORES</p></div>
            <a href="{{ route('alumnos') }}" class="card"><i class="ri-book-open-line"></i><p>ALUMNOS</p></a>
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
