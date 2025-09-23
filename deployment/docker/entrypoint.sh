#!/bin/sh
set -e

# Fix permissions for Laravel storage and cache
chown -R www-data:www-data /var/www/mgn/storage /var/www/mgn/bootstrap/cache
chmod -R g+rwX /var/www/mgn/storage /var/www/mgn/bootstrap/cache

# Set umask for new files/folders
umask 0002

# Execute CMD
exec "$@"