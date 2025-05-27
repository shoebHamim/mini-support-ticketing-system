<?php
require_once __DIR__ . '/../config/Database.php';
class Department
{
    private $db;
    private $table = 'departments';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }


    public function findById($id)
    {
        $statement = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $statement->execute([$id]);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }
    public function getAll()
    {
        $statement = $this->db->query("SELECT * FROM {$this->table}");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name)
    {
        $statement = $this->db->prepare("INSERT INTO {$this->table} (name) VALUES (?)");
        $statement->execute([$name]);
        return [
            'success' => true,
            'message' => 'Department created successfully',
            'department_id' => $this->db->lastInsertId()
        ];
    }

    public function delete($id)
    {
        $statement = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $statement->execute([$id]);
        if ($statement->rowCount() > 0) {
            return [
                'success' => true,
                'message' => 'Department deleted successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Could not delete the department'
            ];
        }
    }
    public function update($id, $name)
    {
        $statement = $this->db->prepare("UPDATE {$this->table} SET name = ? WHERE id = ?");
        $statement->execute([$name, $id]);

        if ($statement->rowCount() > 0) {
            return [
                'success' => true,
                'message' => 'Department updated successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No changes made to department'
            ];
        }
    }
}
