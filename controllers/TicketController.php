<?php
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Department.php';

class TicketController {
  private $ticketModel;
  private $departmentModel;
  public function __construct() {
    $this->ticketModel = new Ticket();
    $this->departmentModel = new Department();
  }

  public function create($user) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['title']) || empty(trim($data['title']))) {
      http_response_code(400);
      echo json_encode(['error' => 'Title is required']);
      return;
    }
    if (!isset($data['description']) || empty(trim($data['description']))) {
      http_response_code(400);
      echo json_encode(['error' => 'Description is required']);
      return;
    }
    if (!isset($data['department_id']) || empty($data['department_id'])) {
      http_response_code(400);
      echo json_encode(['error' => 'Department(department_id) is required']);
      return;
    }
    
    $department = $this->departmentModel->findById($data['department_id']);
    if (!$department) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid department']);
      return;
    }

    $ticketData = [
      'title' => trim($data['title']),
      'description' => trim($data['description']),
      'department_id' => $data['department_id'],
    ];
    
    $result = $this->ticketModel->create($ticketData);
    
    http_response_code($result['success'] ? 201 : 400);
    echo json_encode($result);
  }

  public function assignToSelf($id, $user) {
    if ($user['role'] !== 'admin' && $user['role'] !== 'agent') {
      http_response_code(403);
      echo json_encode(['error' => 'Only agents can be assigned to tickets']);
      return;
    }
    
    $ticket = $this->ticketModel->findById($id);
    if (!$ticket) {
      http_response_code(404);
      echo json_encode(['error' => 'Ticket not found']);
      return;
    }
    
    if ($ticket['user_id'] != null) {
      http_response_code(400);
      echo json_encode(['error' => 'Ticket is already assigned to an agent']);
      return;
    }
    
    $result = $this->ticketModel->assignToAgent($id, $user['id']);
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
  }


  public function updateStatus($id, $user) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['status']) || empty($data['status'])) {
      http_response_code(400);
      echo json_encode(['error' => 'Status is required']);
      return;
    }
     if (!in_array($data['status'], ['open', 'closed','in progress'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Status must be among open, closed and in progress']);
            return;
        }
    $ticket = $this->ticketModel->findById($id);
    if (!$ticket) {
      http_response_code(404);
      echo json_encode(['error' => 'Ticket not found']);
      return;
    }
    if ($user['role'] !== 'admin' && $user['id'] != $ticket['user_id']) {
      http_response_code(403);
      echo json_encode(['error' => 'You do not have permission to update this ticket']);
      return;
    }
    
    $result = $this->ticketModel->updateStatus($id, $data['status']);
    
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
  }

}