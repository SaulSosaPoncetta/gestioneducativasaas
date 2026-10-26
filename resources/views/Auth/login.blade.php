<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar · {{ config('app.name') }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="pagina-login min-vh-100 d-flex align-items-center justify-content-center p-3">

    <div class="card shadow-lg border-0 w-100 login-card">
        <div class="card-body p-4 p-sm-5">

            <div class="text-center mb-4">
                <span class="login-logo mb-3"><i class="bi bi-mortarboard-fill"></i></span>
                <h1 class="h4 mb-1">{{ config('app.name') }}</h1>
                <p class="text-muted small mb-0">Jardín · Primaria · Secundaria · Técnica · Terciario · Universidad</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger py-2 small">
                    <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="usuario" class="form-label">Email o DNI</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="usuario" id="usuario" value="{{ old('usuario') }}"
                               class="form-control @error('usuario') is-invalid @enderror"
                               autocomplete="username" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button" data-ver-clave="#password" title="Mostrar contraseña">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="recordar" id="recordar" value="1">
                        <label class="form-check-label small" for="recordar">Recordarme</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small">¿Olvidaste tu contraseña?</a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
                </button>
            </form>
        </div>
    </div>

</body>
</html>
