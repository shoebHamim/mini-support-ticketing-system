<?php
require_once __DIR__ . '/../models/Note.php';
require_once __DIR__ . '/../models/Ticket.php';

class NoteController {
  private $noteModel;
  private $ticketModel;

  public function __construct() {
    $this->noteModel = new Note();
    $this->ticketModel = new Ticket();
  }

  public function addNote($ticketId, $user) {
    $ticket = $this->ticketModel->findById($ticketId);
    if (!$ticket) {
      http_response_code(404);
      echo json_encode(['error' => 'Ticket not found']);
      return;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['note']) || empty(trim($data['note']))) {
      http_response_code(400);
      echo json_encode(['error' => 'Note content is required']);
      return;
    }

    $noteData = [
      'ticket_id' => $ticketId,
      'user_id' => $user['id'],
      'note' => trim($data['note'])
    ];
    $result = $this->noteModel->create($noteData);
    http_response_code($result['success'] ? 201 : 400);
    echo json_encode($result);
  }

}