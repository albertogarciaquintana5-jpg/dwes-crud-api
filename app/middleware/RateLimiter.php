<?php
namespace App\Middleware;

class RateLimiter {
    // Limitador simple (usa archivos) por clave
    // parámetros: clave (string), maxAttempts (int), decaySeconds (int)
    public static function tooManyAttempts($key, $maxAttempts = 5, $decaySeconds = 300) {
        $file = self::filePath($key);
        $now = time();
        $times = [];
        if (file_exists($file)) {
            $data = @json_decode(@file_get_contents($file), true);
            if (is_array($data)) $times = $data;
        }
        // limpiar entradas antiguas
        $times = array_filter($times, function($t) use ($now, $decaySeconds) { return ($t > $now - $decaySeconds); });
        return count($times) >= $maxAttempts;
    }

    public static function hit($key, $maxAttempts = 5, $decaySeconds = 300) {
        $file = self::filePath($key);
        $now = time();
        $times = [];
        if (file_exists($file)) {
            $data = @json_decode(@file_get_contents($file), true);
            if (is_array($data)) $times = $data;
        }
        // limpiar entradas antiguas
        $times = array_filter($times, function($t) use ($now, $decaySeconds) { return ($t > $now - $decaySeconds); });
        $times[] = $now;
        @file_put_contents($file, json_encode(array_values($times)));
        return count($times);
    }

    public static function clear($key) {
        $file = self::filePath($key);
        if (file_exists($file)) @unlink($file);
    }

    private static function filePath($key) {
        $safe = preg_replace('/[^a-z0-9_\-]/i', '-', $key);
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'crudapi_rl_' . md5($safe) . '.json';
    }
}
