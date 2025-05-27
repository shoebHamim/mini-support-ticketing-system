<?php
require_once __DIR__ . '/../config/Database.php';

class Note {
  private $db;
  private $table = 'ticket_notes';

  public function __construct() {
    $this->db = Database::getConnection();
  }

  public function create($data) {
    $statement = $this->db->prepare(
      "INSERT INTO {$this->table} (ticket_id, user_id, note, created_at) VALUES (?, ?, ?, datetime('now'))"
    );

    try {
      $statement->execute([
        $data['ticket_id'],
        $data['user_id'],
        $data['note']
      ]);
      
      return [
        'success' => true,
        'message' => 'Note added successfully',
        'note_id' => $this->db->lastInsertId()
      ];
    } catch (PDOException $e) {
      return [
        'success' => false,
        'error' => 'Failed to add note'
      ];
    }
  }

}