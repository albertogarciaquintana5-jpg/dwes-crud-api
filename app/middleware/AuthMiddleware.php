<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    public static function requireAuth() {
        // Debe venir la cabecera Authorization: Bearer <token>
        $h = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null);
        if (!$h) {
            http_response_code(401);
            echo json_encode(['error' => 'Missing Authorization header']);
            exit;
        }
        if (!preg_match('/Bearer\s+(.*)$/i', $h, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Malformed Authorization header']);
            exit;
        }
        $token = $matches[1];
        $cfg = require __DIR__ . '/../../config/jwt.php';
        try {
            $decoded = JWT::decode($token, new Key($cfg['secret'], $cfg['algo']));
            // guardo el id de usuario en $_SERVER['AUTH_USER'] por si hace falta
            $_SERVER['AUTH_USER'] = $decoded->sub ?? null;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
    }
}
