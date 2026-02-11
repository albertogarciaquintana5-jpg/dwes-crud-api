<?php
// config/database.php
// Devuelve una instancia PDO leyendo variables de entorno (.env) o usando valores por defecto

function getPDO($useDb = true) {
    // Leer variables de entorno (si no existen, usar valores por defecto para desarrollo)
    $host = getenv('DB_HOST') !== false ? getenv('DB_HOST') : '127.0.0.1';
    $db   = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'crud_api';
    $user = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
    $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $charset = 'utf8mb4';

    if ($useDb) {
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    } else {
        // conectar sin seleccionar una base de datos (útil para crear la BD)
        $dsn = "mysql:host=$host;charset=$charset";
    }
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Mensaje simple para el profesor
        die('Error de conexión a la base de datos: ' . $e->getMessage());
    }
}
