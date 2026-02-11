<?php
namespace App\Controllers;

use App\Models\User;

class UserController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    public function index($query) {
        $limit = isset($query['limit']) ? min(100, (int)$query['limit']) : 100;
        $offset = isset($query['offset']) ? (int)$query['offset'] : 0;
        $users = $this->userModel->all($limit, $offset);
        echo json_encode(['data' => $users]);
    }

    public function show($id) {
        $u = $this->userModel->find($id);
        if (!$u) { http_response_code(404); echo json_encode(['error'=>'Not found']); return; }
        echo json_encode(['data' => $u]);
    }

    public function store($body) {
        $errors = [];
        $errors = array_merge($errors, \App\Validators\Validator::requiredFields($body, ['nombre','email','password']));
        if (!empty($body['email']) && !\App\Validators\Validator::validateEmail($body['email'])) $errors[] = 'El email no tiene un formato válido';
        if (!empty($body['password']) && !\App\Validators\Validator::validatePassword($body['password'], 6)) $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        if (!empty($body['email']) && $this->userModel->existsEmail($body['email'])) $errors[] = 'Email already exists';
        if (count($errors) > 0) { http_response_code(422); echo json_encode(['errors' => $errors]); return; }

        $id = $this->userModel->create($body);
        if (!$id) { http_response_code(500); echo json_encode(['error'=>'Insert failed']); return; }
        http_response_code(201);
        echo json_encode(['data' => $this->userModel->find($id)]);
    }

    public function update($id, $body) {
        $errors = [];
        if (isset($body['email'])) {
            if (!\App\Validators\Validator::validateEmail($body['email'])) $errors[] = 'El email no tiene un formato válido';
            if ($this->userModel->existsEmail($body['email'], $id)) $errors[] = 'Email already exists';
        }
        if (isset($body['password']) && !\App\Validators\Validator::validatePassword($body['password'], 6)) $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        if (count($errors) > 0) { http_response_code(422); echo json_encode(['errors'=>$errors]); return; }

        $ok = $this->userModel->update($id, $body);
        if (!$ok) { http_response_code(500); echo json_encode(['error'=>'Update failed']); return; }
        echo json_encode(['data' => $this->userModel->find($id)]);
    }

    public function destroy($id) {
        $u = $this->userModel->find($id);
        if (!$u) { http_response_code(404); echo json_encode(['error'=>'Not found']); return; }
        $this->userModel->delete($id);
        echo json_encode(['message'=>'Deleted']);
    }
}
