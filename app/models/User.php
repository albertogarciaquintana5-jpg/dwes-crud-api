<?php
namespace App\Models;

class User {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function all($limit = 100, $offset = 0) {
        $stmt = $this->pdo->prepare("SELECT id, nombre, apellido, fecha, telefono, email FROM users ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT id, nombre, apellido, fecha, telefono, email FROM users WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($data) {
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (nombre, apellido, email, password, fecha, telefono) VALUES (?, ?, ?, ?, ?, ?)");
        $ok = $stmt->execute([
            $data['nombre'] ?? null,
            $data['apellido'] ?? null,
            $data['email'] ?? null,
            $hash,
            $data['fecha'] ?? null,
            $data['telefono'] ?? null
        ]);
        if ($ok) return (int)$this->pdo->lastInsertId();
        return false;
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];
        if (isset($data['nombre'])) { $fields[] = 'nombre = ?'; $params[] = $data['nombre']; }
        if (isset($data['apellido'])) { $fields[] = 'apellido = ?'; $params[] = $data['apellido']; }
        if (isset($data['email'])) { $fields[] = 'email = ?'; $params[] = $data['email']; }
        if (isset($data['fecha'])) { $fields[] = 'fecha = ?'; $params[] = $data['fecha']; }
        if (isset($data['telefono'])) { $fields[] = 'telefono = ?'; $params[] = $data['telefono']; }
        if (isset($data['password']) && $data['password'] !== '') { $fields[] = 'password = ?'; $params[] = password_hash($data['password'], PASSWORD_DEFAULT); }

        if (count($fields) === 0) return false;
        $params[] = (int)$id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function existsEmail($email, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, (int)$excludeId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }
        return (bool)$stmt->fetch();
    }
}
