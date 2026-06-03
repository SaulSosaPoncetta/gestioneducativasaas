<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buscar Administador</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filtro.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/alumnosSec.css') }}">
</head>

<body>
    <button class="mobile-menu-btn" id="menu-btn">
    <i class="ri-menu-line"></i>
    </button>

@include('partials.sidebar')

<div class="main">

    <!-- HEADER -->
    <div class="top-bar">
        <h1>Buscar Administrador</h1>
        <button class="btn-primary" onclick="window.location.href=''">Agregar Administrador</button>
    </div>

    <!-- CARD -->
    <div class="card-container">

        <div class="form-grid">

            <select>
                <option id="nombre-Alumno">Nombre</option>
            </select>

            <input class="form-grid" placeholder="DNI"></input>

        </div>

    </div>

    <!-- BOTONES -->
    <div class="form-actions">
        <button class="btn-secondary">Limpiar</button>
        <button class="btn-primary">Filtrar</button>
    </div>

    <div class="table-container">

        <table class="cursada-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Perfil</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>Juan Pérez</td>
                    <td>30123456</td>
                    <td>Titular@gmail.com</td>
                    <td>2284 226478</td>
                    <td>
                        <button class="btn-primary">Ver</button>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>

@include('partials.footer')

</body>
</html>