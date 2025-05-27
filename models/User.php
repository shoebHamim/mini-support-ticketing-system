<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function register($data) {
        $statement = $this->db->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        try {
            $statement->execute([$data['name'], $data['email'], $passwordHash, $data['role']]);
            http_response_code(201);
            return ['message' => 'User registered successfully'];
        } catch (PDOException $e) {
            http_response_code(400);
            return ['error' => 'Email already exists'];
        }
    }

    public function login($data) {
        $statement = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $statement->execute([$data['email']]);
        $user = $statement->fetch();
        if ($user && password_verify($data['password'], $user['password_hash'])) {
            $token = bin2hex(random_bytes(16));
            $tokens = file_exists(__DIR__ . '/../storage/tokens.json') 
                ? json_decode(file_get_contents(__DIR__ . '/../storage/tokens.json'), true)
                : [];
            $tokens[$token] = ['user_id' => $user['id'], 'role' => $user['role']];
            file_put_contents(__DIR__ . '/../storage/tokens.json', json_encode($tokens));
            return ['token' => $token];
        }
        return ['error' => 'Invalid email or password'];
    }

    public function logout($token) {
        $path = __DIR__ . '/../storage/tokens.json';
        $tokens = file_exists($path) 
            ? json_decode(file_get_contents($path), true)
            : [];

        if (isset($tokens[$token])) {
            unset($tokens[$token]);
            file_put_contents($path, json_encode($tokens));
            return ['message' => 'Logged out'];
        }

        return ['error' => 'Invalid token'];
    }
      public static function getByToken($token) {
        $path = __DIR__ . '/../storage/tokens.json';
        
        if (!file_exists($path)) {
            return null;
        }
        
        $tokens = json_decode(file_get_contents($path), true);
        
        if (!isset($tokens[$token])) {
            return null;
        }
        
        $userData = $tokens[$token];
        $userId = $userData['user_id'];
        
        $db = Database::getConnection();
        $statement = $db->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $statement->execute([$userId]);
        $user = $statement->fetch();
        
        if (!$user) {
            return null;
        }
        
        $user['token'] = $token;
        return $user;
    }
 


}
