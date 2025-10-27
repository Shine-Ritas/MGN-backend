#!/bin/bash

# PostgreSQL Backup Script with Compression
# Creates compressed database backup without restrictions

set -e

# Configuration
CONTAINER_NAME="mgn-db"
DB_NAME="mgn"
DB_USER="user"
DB_PASSWORD="password"
BACKUP_DIR="/home/administrator/backups/postgres"


# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Create backup filename with timestamp
timestamp=$(date +'%Y%m%d_%H%M%S')
backup_file="${BACKUP_DIR}/postgres_backup_${timestamp}.backup"

echo "Creating PostgreSQL backup: $backup_file"

# Check database status
echo "Database size:"
docker exec "$CONTAINER_NAME" psql -U "$DB_USER" -d "$DB_NAME" -c "SELECT pg_size_pretty(pg_database_size('$DB_NAME'));" 2>/dev/null || echo "Could not get database size"

# Create backup using the working simple approach
echo "Running pg_dump command (using working simple approach)..."
if docker exec "$CONTAINER_NAME" pg_dump \
    -U "$DB_USER" \
    -d "$DB_NAME" \
    --no-password \
    --format=c \
    --compress=6 \
    --verbose \
    > "$backup_file" 2> "${backup_file}.log"; then
    echo "pg_dump completed successfully"
else
    echo "pg_dump failed with exit code $?"
    echo "Check the log file for error messages:"
    cat "${backup_file}.log"
    exit 1
fi

# Check backup file size
backup_size=$(stat -c%s "$backup_file" 2>/dev/null || echo "0")
if [ "$backup_size" -eq 0 ]; then
    echo "ERROR: Backup file is empty!"
    echo "This usually means pg_dump failed. Check the error messages above."
    exit 1
else
    echo "Backup completed: $backup_file (Size: $backup_size bytes)"
fi