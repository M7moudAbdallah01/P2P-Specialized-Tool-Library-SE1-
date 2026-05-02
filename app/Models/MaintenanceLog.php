<?php

require_once "../../Core/Database.php";

class MaintenanceLog
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getByTool($tool_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM maintenance_logs WHERE tool_id = ?");
        $stmt->bind_param("i", $tool_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>