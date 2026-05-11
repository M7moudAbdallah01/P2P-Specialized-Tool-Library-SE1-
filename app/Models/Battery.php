<?php

require_once "../../../Core/database.php";

class Battery
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getByTool($tool_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM battery_logs WHERE tool_id = ?");
        $stmt->bind_param("i", $tool_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function update($tool_id, $health_status, $last_checked)
    {
        $stmt = $this->conn->prepare("UPDATE battery_logs SET status = ?, last_checked = ? WHERE tool_id = ?");
        $stmt->bind_param("ssi", $health_status, $last_checked, $tool_id);
        return $stmt->execute();
    }

}
