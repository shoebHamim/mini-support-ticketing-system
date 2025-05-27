<?php
require_once __DIR__ . '/../config/Database.php';
class User
{
  private $db;
  private $table = 'users';

  public function __construct()
  {
    $this->db = Database::getConnection();
  }

  public function register($data)
  {
    $statement = $this->db->prepare(
      "INSERT INTO {$this->table} (name, email, password_hash, role) VALUES (?, ?, ?, ?)"
    );
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

    try {
      $statement->execute([
        $data['name'],
        $data['email'],
        $passwordHash,
        $data['role']
      ]);
      return [
        'success' => true,
        'message' => 'User registered successfully',
        'user_id' => $this->db->lastInsertId()
      ];
    } catch (PDOException $e) {
      return [
        'success' => false,
        'error' => 'Email already exists'
      ];
    }
  }
  public function findByEmail($email)
  {
    $statement = $this->db->prepare(
      "SELECT * FROM {$this->table} WHERE email = ?"
    );
    $statement->execute([$email]);
    return $statement->fetch(PDO::FETCH_ASSOC);
  }

  public function findById($id)
  {
    $statement = $this->db->prepare(
      "SELECT id, name, email, role FROM {$this->table} WHERE id = ?"
    );
    $statement->execute([$id]);
    return $statement->fetch(PDO::FETCH_ASSOC);
  }

  public static function getByToken($token)
  {
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
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
      return null;
    }

    $user['token'] = $token;
    return $user;
  }



}
