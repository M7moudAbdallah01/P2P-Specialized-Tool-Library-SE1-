<?php

// الخطة: نطلع خطوتين لبره (واحدة من Models وواحدة من app) عشان نلاقي Core
require_once __DIR__ . "/../../Core/database.php";

class User
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // جلب كل المستخدمين (ماعدا الأدمن)
    public function getAll()
    {
        $result = $this->conn->query("SELECT user_id, name, email, status FROM users WHERE role != 'admin'");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // تحديث حالة المستخدم
    public function updateStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }
}