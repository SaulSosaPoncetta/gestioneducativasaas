<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Inicio') · {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('estilos')
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Navbar superior: secciones como desplegables; en celular se colapsa --}}
    <nav class="navbar navbar-expand-lg navbar-dark navbar-principal sticky-top shadow-sm">
        <div class="container-fluid">

            <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center gap-2">
                <i class="bi bi-mortarboard-fill"></i>
                <span>{{ config('app.name') }}</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPrincipal"
                    aria-controls="navPrincipal" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navPrincipal">

                @include('layouts.partials.menu')

                {{-- Usuario --}}
                <ul class="navbar-nav ms-lg-auto">
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar">{{ auth()->user()->iniciales }}</span>
                            <span class="text-start lh-sm">
                                <span class="d-block small fw-semibold">{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</span>
                                <span class="d-block small opacity-75">{{ auth()->user()->rol->label() }}</span>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            @if ($institucionActual ?? null)
                                <li><h6 class="dropdown-header text-wrap">{{ $institucionActual->nombre }}</h6></li>
                            @endif

                            @if (Route::has('perfil.edit'))
                                <li><a class="dropdown-item" href="{{ route('perfil.edit') }}"><i class="bi bi-person me-2"></i>Mis datos</a></li>
                            @endif

                            @if (auth()->user()->esSuperAdmin() && ($institucionActual ?? null))
                                <li>
                                    <form method="POST" action="{{ route('institucion.salir') }}">
                                        @csrf
                                        <button class="dropdown-item"><i class="bi bi-box-arrow-left me-2"></i>Salir de {{ $institucionActual->nombre_visible }}</button>
                                    </form>
                                </li>
                            @endif

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Franja con la institución en uso --}}
    <div class="barra-institucion border-bottom bg-white no-print">
        <div class="container-fluid py-2 d-flex align-items-center gap-2 small">
            @if ($institucionActual ?? null)
                <i class="bi bi-building text-primary"></i>
                <span class="fw-semibold text-truncate">{{ $institucionActual->nombre }}</span>
                @if (auth()->user()->esSuperAdmin())
                    <span class="badge text-bg-warning ms-1">Modo superadmin</span>
                @endif
            @else
                <i class="bi bi-grid text-primary"></i>
                <span class="fw-semibold">Panel MiGestión</span>
            @endif
        </div>
    </div>

    <main class="flex-grow-1">
        <div class="container-fluid p-3 p-md-4">
            @if (session('ok'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-1"></i> {{ session('ok') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('contenido')
        </div>
    </main>

    <footer class="text-center text-muted small py-3 no-print">
        {{ config('app.name') }} · MiGestión © {{ date('Y') }}
    </footer>

    {{-- Los scripts de las vistas usan window.bootstrap dentro de DOMContentLoaded (app.js es módulo y carga diferido) --}}
    @stack('scripts')
</body>
</html>
