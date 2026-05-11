<?php
class Category {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function getAllCategories() {
        $sql = "SELECT * FROM category ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCategory($name, $description) {
        $sql = "INSERT INTO category (name, description) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $description]);
    }
}