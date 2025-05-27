<?php
require_once __DIR__ . '/../models/User.php';

class AuthMiddleware {
    public static function checkAuth() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Authorization token missing']);
            exit;
        }

        $token = trim($headers['Authorization']);
        $user = User::getByToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            exit;
        }

        return $user;
    }

    public static function checkAdmin() {
        $user = self::checkAuth();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            exit;
        }
        return $user;
    }
}
