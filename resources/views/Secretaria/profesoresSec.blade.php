<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar profesor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/Secretaria/sideBar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filtro.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/botones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Secretaria/profesoresSec.css') }}">
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
        <h1>Buscar Profesor</h1>
        <button class="btn-primary" onclick="window.location.href='{{ route('profesoresAgregar') }}'">Agregar Profesor</button>
    </div>

    <!-- CARD PROFESORES -->
    <div class="card-container">

        <div class="form-grid">

            <input type="text" placeholder="Nombre y Apellido">

            <input type="text" placeholder="DNI">

            <select>
                <option>Tipo</option>
                <option>Titular</option>
                <option>Titular Interino</option>
                <option>Provisional</option>
                <option>Suplente</option>
            </select>
            
            

        </div>

    </div>

    <!-- CARD CARRERA -->
    <div class="card-container">

        <div class="form-grid">

            <select>
                <option>Plan de estudio</option>
            </select>

            <div class="row-small">
                <input type="text" placeholder="Desde">
                <input type="text" placeholder="Hasta">
            </div>

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
                    <th>Tipo</th>
                    <th>Materia</th>
                    <th>Perfil</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>Juan Pérez</td>
                    <td>30123456</td>
                    <td>Titular</td>
                    <td>Matemática</td>
                    <td>
                        <button class="btn-primary">Ver</button>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

@include('partials.footer')

</body>
</html>
