<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('tryCatch')) {

    function tryCatch(callable $callback, ?string $message = null, bool $withException = false): mixed
    {
        try {
            return $callback();
        } catch (\Exception $e) {

            $status = 500;
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $status = 422;

                return response()->json(
                    [
                        'message' => 'Validation failed',
                        'errors' => $e->errors(),
                    ], $status
                );
            }

            if ($withException) {
                $message .= ' '.$e->getMessage();
            }

            return response()->json(['message' => $message], $status);
        }
    }

}

if (! function_exists('appDriver')) {

    function appDriver(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        $disk = config('control.mongou_storage');
        if (! is_string($disk)) {
            $disk = 'local';
        }

        return Storage::disk($disk);
    }

}

if (! function_exists('generateSubMogouFolder')) {
    function generateStorageFolder(string $prefixFolder, string $folder): string
    {
        return "$prefixFolder/$folder";
    }
}

if (! function_exists('enumValue')) {
    function enumValue(mixed $enum): mixed
    {
        return $enum instanceof \BackedEnum ? $enum->value : $enum;
    }
}

if (! function_exists('ensureDirectoryPermissions')) {
    /**
     * Ensure a directory has proper permissions (775)
     * This fixes the umask issue where directories are created with 700 permissions
     */
    function ensureDirectoryPermissions(string $path): void
    {
        if (is_dir($path)) {
            chmod($path, 0775);

            // Also fix parent directories if they exist and are too restrictive
            $parentPath = dirname($path);
            if ($parentPath !== $path && is_dir($parentPath)) {
                $currentPerms = fileperms($parentPath) & 0777;
                if ($currentPerms < 0755) {
                    chmod($parentPath, 0775);
                }
            }
        }
    }
}

if (! function_exists('fGetUptime')) {
    function fGetUptime(): string
    {
        $uptime = '';

        if (is_readable('/proc/uptime')) {
            $str = @file_get_contents('/proc/uptime');
            $num = floatval($str);
            $secs = $num % 60;
            $num = (int) ($num / 60);
            $mins = $num % 60;
            $num = (int) ($num / 60);
            $hours = $num % 24;
            $num = (int) ($num / 24);
            $days = $num;

            $uptime = $days.' days, '.$hours.' hours & '.$mins.' minutes';
        } // /is_readable('/proc/uptime')

        return $uptime;
    }
}

if (! function_exists('formatBytes')) {
    function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
