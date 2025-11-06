#!/bin/sh
set -e

# Set umask so new files/folders are group-writable
umask 0002

echo "[entrypoint] Effective umask: $(umask)"

# Ensure storage and cache directories exist
mkdir -p /var/www/mgn/storage /var/www/mgn/bootstrap/cache

# Only change ownership if running as root (during initial setup)
# If running as www-data, just ensure proper permissions without chown
if [ "$(id -u)" = "0" ]; then
    echo "[entrypoint] Running as root, setting ownership..."
    chown -R www-data:www-data /var/www/mgn/storage /var/www/mgn/bootstrap/cache
    chmod -R 775 /var/www/mgn/storage /var/www/mgn/bootstrap/cache
else
    echo "[entrypoint] Running as $(id -un), setting permissions only..."
    chmod -R 775 /var/www/mgn/storage /var/www/mgn/bootstrap/cache 2>/dev/null || true
fi

# Execute CMD from Dockerfile
exec "$@"
