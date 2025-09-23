#!/bin/sh
umask 0002   # must be first, before anything else
set -e

# Ensure storage and cache have proper permissions
mkdir -p /var/www/mgn/storage /var/www/mgn/bootstrap/cache
chown -R www-data:www-data /var/www/mgn/storage /var/www/mgn/bootstrap/cache
chmod -R 775 /var/www/mgn/storage /var/www/mgn/bootstrap/cache

# Debug: print umask so we know it's set correctly
umask

# Start main process
exec "$@"
