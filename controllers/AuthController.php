<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);
        $user = new User();
        $result = $user->register($data);
        echo json_encode($result);
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        $user = new User();
        $result = $user->login($data);
        echo json_encode($result);
    }

    public function logout() {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? '';
        $user = new User();
        $result = $user->logout($token);
        echo json_encode($result);
    }
}
