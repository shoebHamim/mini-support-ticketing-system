<?php
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

class DepartmentController {
    public function index() {
        AuthMiddleware::checkAuth();
        echo json_encode(Department::getAll());
    }

    public function create() {
        AuthMiddleware::checkAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? null;

        if (!$name) {
            http_response_code(400);
            echo json_encode(['error' => 'Department name required']);
            return;
        }

        Department::create($name);
        echo json_encode(['message' => 'Department created']);
    }

    public function delete($id) {
        AuthMiddleware::checkAdmin();
        Department::delete($id);
        echo json_encode(['message' => 'Department deleted']);
    }
}
