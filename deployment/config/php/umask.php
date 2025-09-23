<?php
/**
 * Set proper umask for directory creation
 * This file is auto-prepended to all PHP requests via php.ini
 * 
 * umask(0002) ensures:
 * - Files are created with 664 permissions (rw-rw-r--)
 * - Directories are created with 775 permissions (rwxrwxr-x)
 */
umask(0002);
