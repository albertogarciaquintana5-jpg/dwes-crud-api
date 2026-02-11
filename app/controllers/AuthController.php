<?php
namespace App\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;

class AuthController {
    private $userModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    public function register($body) {
        $errors = [];
        // Campos obligatorios
        $errors = array_merge($errors, \App\Validators\Validator::requiredFields($body, ['nombre','email','password']));
        // Formatos válidos
        if (!empty($body['email']) && !\App\Validators\Validator::validateEmail($body['email'])) $errors[] = 'El email no tiene un formato válido';
        if (!empty($body['password']) && !\App\Validators\Validator::validatePassword($body['password'], 6)) $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        if (!empty($body['email']) && $this->userModel->existsEmail($body['email'])) $errors[] = 'El email ya está registrado';

        if (count($errors) > 0) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }
        $id = $this->userModel->create($body);
        if (!$id) { http_response_code(500); echo json_encode(['error' => 'Creación fallida']); return; }
        // Devolver token (JWT)
        $token = $this->makeToken($id, $body['email']);
        http_response_code(201);
        echo json_encode(['token' => $token]);
    }

    public function login($body) {
        $errors = [];
        $errors = array_merge($errors, \App\Validators\Validator::requiredFields($body, ['email','password']));
        if (!empty($body['email']) && !\App\Validators\Validator::validateEmail($body['email'])) $errors[] = 'El email no tiene un formato válido';
        if (!empty($body['password']) && !\App\Validators\Validator::validatePassword($body['password'], 6)) $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        if (count($errors) > 0) { http_response_code(422); echo json_encode(['errors' => $errors]); return; }

        // Limitar intentos por IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'login:' . $ip;
        $max = 5; $decay = 300; // 5 intentos cada 5 minutos
        if (\App\Middleware\RateLimiter::tooManyAttempts($key, $max, $decay)) {
            // calcular tiempo de espera
            $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'crudapi_rl_' . md5($key) . '.json';
            $times = [];
            if (file_exists($file)) $times = json_decode(file_get_contents($file), true) ?: [];
            $earliest = count($times) ? min($times) : time();
            $retryAfter = ($earliest + $decay) - time();
            header('Retry-After: ' . max(0, $retryAfter));
            http_response_code(429);
            echo json_encode(['error' => 'Too many attempts. Try again later.']);
            return;
        }

        $user = $this->userModel->findByEmail($body['email']);
        if (!$user || !password_verify($body['password'], $user['password'])) {
            // registrar intento fallido
            \App\Middleware\RateLimiter::hit($key, $max, $decay);
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales incorrectas']);
            return;
        }

        // login correcto: borrar intentos
        \App\Middleware\RateLimiter::clear($key);
        $token = $this->makeToken($user['id'], $user['email']);
        echo json_encode(['token' => $token]);
    }

    private function makeToken($userId, $email) {
        $cfg = require __DIR__ . '/../../config/jwt.php';
        $now = time();
        $payload = [
            'iat' => $now,
            'exp' => $now + ($cfg['exp_seconds'] ?? 3600),
            'sub' => $userId,
            'email' => $email
        ];
        return JWT::encode($payload, $cfg['secret'], $cfg['algo']);
    }
}
