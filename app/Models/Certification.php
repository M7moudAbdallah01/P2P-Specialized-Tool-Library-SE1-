<?php

require_once "../../Core/Database.php";

class Certification
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getByTool($tool_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM certifications WHERE tool_id = ?");
        $stmt->bind_param("i", $tool_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
