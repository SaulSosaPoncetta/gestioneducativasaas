#!/usr/bin/env bash
#
# Deploy de Gestión Educativa (geducativa.migestion.com.ar) desde la rama main.
# Uso, en el VPS:   cd /var/www/gestioneducativa && ./deploy.sh
#
set -euo pipefail

APP_DIR="/var/www/gestioneducativa"
RAMA="main"
PHP_FPM="php8.4-fpm"
USUARIO_WEB="www-data"

cd "$APP_DIR"

echo "==> Gestión Educativa: deploy desde '$RAMA'"

# Si algo falla, la aplicación vuelve a quedar en línea.
volver_en_linea() { php artisan up >/dev/null 2>&1 || true; }
trap volver_en_linea EXIT

php artisan down --retry=15 || true

echo "==> Código"
git fetch origin "$RAMA"
git reset --hard "origin/$RAMA"

echo "==> Dependencias PHP"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> Frontend (Vite)"
if ! command -v npm >/dev/null 2>&1; then
    echo "ERROR: npm no está instalado. Ver deploy/LEEME-deploy.md (instalar Node 22)."
    exit 1
fi
npm ci --no-audit --no-fund
npm run build
rm -f public/hot

echo "==> Base de datos"
php artisan migrate --force

echo "==> Storage"
[ -L public/storage ] || php artisan storage:link
mkdir -p storage/app/private/comunicados storage/framework/{cache,sessions,views} storage/logs

echo "==> Caché de Laravel"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Permisos"
sudo chown -R "$USER":"$USUARIO_WEB" storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache

echo "==> PHP-FPM"
sudo systemctl reload "$PHP_FPM"

php artisan up
trap - EXIT

echo "==> Listo: https://geducativa.migestion.com.ar"
