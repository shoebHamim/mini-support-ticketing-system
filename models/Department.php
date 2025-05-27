<?php
require_once __DIR__ . '/../core/Database.php';

class Department {
    public static function getAll() {
        $db = Database::getConnection();
        return $db->query("SELECT * FROM departments")->fetchAll();
    }

    public static function create($name) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO departments (name) VALUES (?)");
        $stmt->execute([$name]);
    }

    public static function delete($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->execute([$id]);
    }
}
