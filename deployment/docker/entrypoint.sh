#!/bin/sh
set -e

# Set umask so new files/folders are group-writable
umask 0002

echo "[entrypoint] Effective umask: $(umask)"

# Ensure storage and cache have proper permissions
mkdir -p /var/www/mgn/storage /var/www/mgn/bootstrap/cache
chown -R www-data:www-data /var/www/mgn/storage /var/www/mgn/bootstrap/cache
chmod -R 775 /var/www/mgn/storage /var/www/mgn/bootstrap/cache

# Execute CMD from Dockerfile
exec "$@"
