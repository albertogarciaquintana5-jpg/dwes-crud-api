<?php
// index público: router muy simple estilo REST
// Comprobar autoload de Composer
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode(['error' => 'Dependencies not installed. Run "composer install" in project root.']);
    exit;
}
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$pdo = getPDO();
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$script = trim(parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH), '/');
// Calcular ruta relativa
$rel = preg_replace('#^' . preg_quote($script, '#') . '#', '', $path);
$parts = $rel === '' ? [] : array_values(array_filter(explode('/', $rel)));

$resource = $parts[0] ?? null;
$id = isset($parts[1]) ? (int)$parts[1] : null;
$method = $_SERVER['REQUEST_METHOD'];

// Leer body JSON (si aplica)
$body = null;
if (in_array($method, ['POST','PUT'])) {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true);
    if ($raw && $body === null) { http_response_code(400); echo json_encode(['error'=>'Invalid JSON']); exit; }
}

// Rutas básicas
if ($resource === 'auth') {
    $auth = new AuthController($pdo);
    if ($parts[1] === 'register' && $method === 'POST') { $auth->register($body); exit; }
    if ($parts[1] === 'login' && $method === 'POST') { $auth->login($body); exit; }
    http_response_code(404); echo json_encode(['error'=>'Not found']); exit;
}

if ($resource === 'users' || $resource === 'user') {
    $userCtrl = new UserController($pdo);
    // Protejo rutas /users con JWT
    AuthMiddleware::requireAuth();
    if ($method === 'GET' && !$id) { $userCtrl->index($_GET); exit; }
    if ($method === 'GET' && $id) { $userCtrl->show($id); exit; }
    if ($method === 'POST' && !$id) { $userCtrl->store($body); exit; }
    if ($method === 'PUT' && $id) { $userCtrl->update($id, $body); exit; }
    if ($method === 'DELETE' && $id) { $userCtrl->destroy($id); exit; }
    http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit;
}

// Por defecto
http_response_code(404);
echo json_encode(['error' => 'API root: use /auth/login, /auth/register or /users']);
