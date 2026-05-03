<?php
require_once __DIR__ . "/../../Core/database.php";

class Zone {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // إضافة منطقة جديدة
    public function addZone($name) {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO zones (zone_name) VALUES (?)";
        $stmt = $db->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $name);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    // ضفنا كلمة static هنا عشان تحل مشكلة الـ Static Call في الـ View
    public static function getAll() {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM zones ORDER BY zone_id DESC";
        $result = $db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}