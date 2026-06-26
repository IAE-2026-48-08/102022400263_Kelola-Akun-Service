#!/bin/sh
set -e

# =============================================================================
# docker-entrypoint.sh — Account Service (IAE Tugas 2)
# Dijalankan setiap kali container start.
# Generate Swagger spec di runtime agar storage/ selalu terisi dengan benar,
# tidak bergantung pada build-time (yang bisa tertimpa volume mount).
# =============================================================================

echo "[entrypoint] Fixing storage & cache permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "[entrypoint] Ensuring storage/api-docs directory exists..."
mkdir -p /var/www/html/storage/api-docs
chown -R www-data:www-data /var/www/html/storage/api-docs

echo "[entrypoint] Generating Swagger / OpenAPI spec..."
php /var/www/html/artisan l5-swagger:generate || echo "[entrypoint] WARNING: l5-swagger:generate failed, continuing..."

echo "[entrypoint] Clearing Lighthouse schema cache..."
php /var/www/html/artisan lighthouse:clear-cache || true

echo "[entrypoint] Starting Apache..."
exec apache2-foreground
