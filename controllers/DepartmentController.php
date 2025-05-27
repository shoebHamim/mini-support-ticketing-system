<?php
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

class DepartmentController {
    private $departmentModel;
    
    public function __construct() {
        $this->departmentModel = new Department();
    }

    public function getAll() {
        $departments = $this->departmentModel->getAll();
        http_response_code(200);
        echo json_encode($departments);
    }
    

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['name']) || empty(trim($data['name']))) {
            http_response_code(400);
            echo json_encode(['error' => 'Department name is required']);
            return;
        }
        $result = $this->departmentModel->create($data['name']);
        http_response_code($result['success'] ? 201 : 400);
        echo json_encode($result);
    }
    
    public function delete($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Department ID is required']);
            return;
        }
        $department = $this->departmentModel->findById($id);
        if (!$department) {
            http_response_code(404);
            echo json_encode(['error' => 'Department not found']);
            return;
        }
        $result = $this->departmentModel->delete($id);
        http_response_code($result['success'] ? 200 : 500);
        echo json_encode($result);
    }

    public function update($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Department ID is required']);
            return;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['name']) || empty(trim($data['name']))) {
            http_response_code(400);
            echo json_encode(['error' => 'Department name is required']);
            return;
        }
        $department = $this->departmentModel->findById($id);
        if (!$department) {
            http_response_code(404);
            echo json_encode(['error' => 'Department not found']);
            return;
        }
        
        $result = $this->departmentModel->update($id, $data['name']);
        http_response_code($result['success'] ? 200 : 500);
        echo json_encode($result);
    }

}