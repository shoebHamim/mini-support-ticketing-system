<?php
require_once __DIR__ . '/../models/User.php';

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        if (!in_array($data['role'], ['admin', 'agent'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Role must be either admin or agent']);
            return;
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }
        $result = $this->userModel->register($data);
        http_response_code($result['success'] ? 201 : 400);
        echo json_encode($result);
    }

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password required']);
            return;
        }

        $result = $this->userModel->findByEmail($data['email']);

        if (!$result) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
            return;
        }

        if (password_verify($data['password'], $result['password_hash'])) {
            $token = $this->generateToken();
            $this->storeUserToken($token, $result['id'], $result['role']);
            http_response_code(200);
            echo json_encode(['token' => $token]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
        }
    }

    public function logout($user)
    {
        $token = $user['token'];
        $result = $this->removeUserToken($token);
        if ($result) {
            http_response_code(200);
            echo json_encode(['message' => 'Logged out successfully']);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
        }
    }

    private function generateToken()
    {
        return bin2hex(random_bytes(16));
    }

    private function storeUserToken($token, $userId, $userRole)
    {
        $path = __DIR__ . '/../storage/tokens.json';
        $tokens = file_exists($path)
            ? json_decode(file_get_contents($path), true)
            : [];

        $tokens[$token] = [
            'user_id' => $userId,
            'role' => $userRole,
            'created_at' => time()
        ];

        file_put_contents($path, json_encode($tokens));
        return true;
    }

    private function removeUserToken($token)
    {
        $path = __DIR__ . '/../storage/tokens.json';

        if (!file_exists($path)) {
            return false;
        }

        $tokens = json_decode(file_get_contents($path), true);

        if (!isset($tokens[$token])) {
            return false;
        }

        unset($tokens[$token]);
        file_put_contents($path, json_encode($tokens));
        return true;
    }
}
