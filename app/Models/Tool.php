<?php

require_once "../../Core/Database.php";

class Tool
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tools WHERE tool_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getAll()
    {
        $result = $this->conn->query("SELECT * FROM tools");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>