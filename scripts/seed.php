<?php
// Script sencillo para aplicar migraciones y asegurarse de usuarios de prueba
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

echo "Aplicando migraciones y creando usuarios de prueba si es necesario...\n";
// Conectar sin especificar la BD para poder ejecutar CREATE DATABASE si hace falta
$pdo = getPDO(false);

// Ejecutar SQL de migración (si existe)
$sql = @file_get_contents(__DIR__ . '/../migrations/ejecutarsql.sql');
if ($sql) {
    try {
        $pdo->exec($sql);
        echo "Migraciones ejecutadas (incluye creación de usuario Pilar).\n";
    } catch (Exception $e) {
        echo "Error al ejecutar migraciones: " . $e->getMessage() . "\n";
    }
} else {
    echo "No se encontró migrations/ejecutarsql.sql\n";
}

// Comprobar que Pilar existe
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
$stmt->execute(['email' => 'pilar@example.com']);
$hash = password_hash('123456', PASSWORD_DEFAULT);
if ($stmt->fetch()) {
    echo "Usuario Pilar ya existe (email: pilar@example.com). Actualizando contraseña a '123456'.\n";
    $upd = $pdo->prepare('UPDATE users SET password = :password WHERE email = :email');
    $upd->execute(['password' => $hash, 'email' => 'pilar@example.com']);
    echo "Contraseña actualizada.\n";
} else {
    // Esto normalmente no debería ejecutarse porque la migración la crea
    $ins = $pdo->prepare('INSERT INTO users (nombre, apellido, email, password, fecha, telefono) VALUES (:nombre, :apellido, :email, :password, CURDATE(), :telefono)');
    $ins->execute([
        'nombre' => 'Pilar',
        'apellido' => 'Gonzalez',
        'email' => 'pilar@example.com',
        'password' => $hash,
        'telefono' => '600123456'
    ]);
    if ($ins->rowCount()) {
        echo "Usuario Pilar creado con contraseña '123456'.\n";
    } else {
        echo "No se pudo crear Pilar desde el script, revisa permisos.\n";
    }
} 

echo "Listo. Para probar, inicia el servidor y haz login con:\n  email: pilar@example.com\n  password: 123456";