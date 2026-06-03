<div class="sidebar">

    <div>
        <div class="top-icons">
            {{-- <i class="ri-menu-line" id="menu-btn"></i> --}}
            <i class="ri-logout-box-r-line"></i>
        </div>

        <div class="profile">
            <img src="https://via.placeholder.com/130">
        </div>

            <div class="menu">
                <a href="#"><i class="ri-book-line"></i> Carrera</a>
                <a href="#"><i class="ri-calendar-line"></i> Ciclo Lectivo</a>
                <a href="{{ route('cursada') }}"><i class="ri-book-open-line"></i> Cursada</a>
                <a href="#"><i class="ri-award-line"></i> Finales</a>
                {{-- <a href="{{ route('alumnos') }}"><i class="ri-graduation-cap-line"></i> Alumnos</a> --}}
                <a href="#"><i class="ri-user-line"></i> Usuarios</a>
                <a href="#"><i class="ri-graduation-cap-line"></i> Egresados</a>
                <a href="#"><i class="ri-file-line"></i> Constancias</a>
                <a href="#"><i class="ri-history-line"></i> Auditoria</a>
                <a href="#"><i class="ri-home-line"></i> Mis datos</a>
            </div>
    </div>

</div>

<div class="overlay" id="overlay"></div>
<script src="{{ asset('js/sidebar.js') }}"></script>
