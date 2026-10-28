# Deploy de Gestión Educativa — geducativa.migestion.com.ar

VPS Hostinger · Ubuntu · PHP 8.4 · MySQL · Nginx · carpeta `/var/www/gestioneducativa` · rama `main`.

## Deploys siguientes (lo de todos los días)

```bash
cd /var/www/gestioneducativa && ./deploy.sh
```

## Primera vez (sistema nuevo)

### 1. Backup de lo que haya

```bash
mysqldump -u root -p gestioneducativa > ~/backup-gestioneducativa-$(date +%Y%m%d).sql
sudo cp -a /var/www/gestioneducativa ~/gestioneducativa-viejo-$(date +%Y%m%d)
```

### 2. Node 22 (para compilar Vite), si no está

```bash
node -v || (curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash - && sudo apt-get install -y nodejs)
```

### 3. Extensiones de PHP

```bash
sudo apt-get install -y php8.4-{mysql,mbstring,xml,curl,zip,bcmath,intl,gd}
```

### 4. Base de datos limpia

El esquema nuevo no tiene nada que ver con el anterior: se arranca de cero.

```sql
DROP DATABASE IF EXISTS gestioneducativa;
CREATE DATABASE gestioneducativa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'gestioneducativa'@'localhost' IDENTIFIED BY 'CLAVE_SEGURA';
GRANT ALL PRIVILEGES ON gestioneducativa.* TO 'gestioneducativa'@'localhost';
FLUSH PRIVILEGES;
```

### 5. Código y .env

```bash
cd /var/www/gestioneducativa
git remote -v                       # tiene que ser SaulSosaPoncetta/gestioneducativasaas
git fetch origin main && git reset --hard origin/main
cp .env.production.example .env     # completar DB_PASSWORD y MAIL_PASSWORD
composer install --no-dev --optimize-autoloader
php artisan key:generate
chmod +x deploy.sh
```

### 6. Primer deploy y superadministrador

```bash
./deploy.sh
php artisan sgi:superadmin          # pide email y contraseña (no usar el seeder de demo)
```

Después se entra con ese usuario, se crea cada institución y su administrador.

> El `DatabaseSeeder` carga datos de prueba con clave `password`: **no correrlo en producción.**

### 7. Nginx y PHP

- Revisar el sitio contra `deploy/nginx-geducativa.conf` (sobre todo `root .../public` y `client_max_body_size 12M`).
- En `/etc/php/8.4/fpm/php.ini`: `upload_max_filesize = 12M` y `post_max_size = 12M`.

```bash
sudo nginx -t && sudo systemctl reload nginx && sudo systemctl reload php8.4-fpm
```

### 8. Tareas programadas (limpieza de auditoría)

```bash
crontab -e
# agregar:
* * * * * cd /var/www/gestioneducativa && php artisan schedule:run >> /dev/null 2>&1
```

## Controles después del deploy

- `https://geducativa.migestion.com.ar` muestra el login.
- `https://geducativa.migestion.com.ar/verificar` abre sin iniciar sesión.
- Emitir una constancia y escanear el QR: tiene que abrir `https://geducativa...`.
- Publicar un comunicado con email y ver que llegue.
- `tail -f storage/logs/laravel-$(date +%F).log` sin errores.

## GestiónAula

En cada institución: **Mi institución → Integración GestiónAula → Generar token**, y cargarlo en GestiónAula con la URL `https://geducativa.migestion.com.ar/api/v1`.
