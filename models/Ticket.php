<?php
require_once __DIR__ . '/../config/Database.php';

class Ticket
{
  private $db;
  private $table = 'tickets';

  public function __construct()
  {
    $this->db = Database::getConnection();
  }

  public function create($data)
  {
    $statement = $this->db->prepare(
      "INSERT INTO {$this->table} (title, description, status, department_id, created_at) 
       VALUES (?, ?, 'open', ?, datetime('now'))"
    );

    try {
      $statement->execute([
        $data['title'],
        $data['description'],
        $data['department_id']
      ]);

      return [
        'success' => true,
        'message' => 'Ticket created successfully',
        'ticket_id' => $this->db->lastInsertId()
      ];
    } catch (PDOException $e) {
      return [
        'success' => false,
        'error' => 'Failed to create ticket,something went wrong!'
      ];
    }
  }
  public function getAll($filters = [])
  {
    $sql = "SELECT t.*, d.name AS department_name, u.name AS creator_name, 
            a.name AS assigned_agent_name 
            FROM {$this->table} t
            LEFT JOIN departments d ON t.department_id = d.id
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN users a ON t.assigned_to = a.id";

    $conditions = [];
    $params = [];

    // Apply filters if provided
    if (isset($filters['status']) && !empty($filters['status'])) {
      $conditions[] = "t.status = ?";
      $params[] = $filters['status'];
    }

    if (isset($filters['department_id']) && !empty($filters['department_id'])) {
      $conditions[] = "t.department_id = ?";
      $params[] = $filters['department_id'];
    }

    if (isset($filters['user_id']) && !empty($filters['user_id'])) {
      $conditions[] = "t.user_id = ?";
      $params[] = $filters['user_id'];
    }

    if (isset($filters['assigned_to']) && !empty($filters['assigned_to'])) {
      $conditions[] = "t.assigned_to = ?";
      $params[] = $filters['assigned_to'];
    }

    // Add WHERE clause if there are conditions
    if (!empty($conditions)) {
      $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY 
              CASE 
                WHEN t.status = 'open' THEN 1
                WHEN t.status = 'in progress' THEN 2
                WHEN t.status = 'closed' THEN 3
              END,
              t.created_at DESC";

    $statement = $this->db->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function findById($id)
  {
    $statement = $this->db->prepare(
      "SELECT t.*, d.name AS department_name
       FROM {$this->table} t
       LEFT JOIN departments d ON t.department_id = d.id
       WHERE t.id = ?"
    );

    $statement->execute([$id]);
    return $statement->fetch(PDO::FETCH_ASSOC);
  }


  public function assignToAgent($ticketId, $agentId)
  {
    $statement = $this->db->prepare(
      "UPDATE {$this->table} SET 
       user_id = ?,
       status = CASE WHEN status = 'open' THEN 'in progress' ELSE status END
       WHERE id = ?"
    );

    try {
      $statement->execute([$agentId, $ticketId]);

      if ($statement->rowCount() > 0) {
        return [
          'success' => true,
          'message' => 'Ticket assigned successfully'
        ];
      } else {
        return [
          'success' => false,
          'error' => 'Ticket not found'
        ];
      }
    } catch (PDOException $e) {
      return [
        'success' => false,
        'error' => 'Failed to assign ticket'
      ];
    }
  }


  public function updateStatus($ticketId, $status)
  {
    $statement = $this->db->prepare(
      "UPDATE {$this->table} SET status = ? WHERE id = ?"
    );

    try {
      $statement->execute([$status, $ticketId]);

      if ($statement->rowCount() > 0) {
        return [
          'success' => true,
          'message' => 'Ticket status updated successfully'
        ];
      } else {
        return [
          'success' => false,
          'error' => 'Ticket not found'
        ];
      }
    } catch (PDOException $e) {
      return [
        'success' => false,
        'error' => 'Failed to update ticket status'
      ];
    }
  }
}
