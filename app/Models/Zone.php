<?php
require_once __DIR__ . "/../../Core/database.php";

class Zone {
    public static function addZone($name){
        $db = Database::getInstance()->getConnection();
        $safe_name = mysqli_real_escape_string($db, $name);
        $query = "INSERT INTO zones (zone_name) VALUES ('$safe_name')";
        return mysqli_query($db, $query);
    }

    public static function getAll(){
        $db = Database::getInstance()->getConnection();
        $query = "SELECT * FROM zones";
        $result = mysqli_query($db, $query);
        return ($result) ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    }

    public static function updateZone($id, $newName) {
        $db = Database::getInstance()->getConnection();
        $query = "UPDATE zones SET zone_name = '".mysqli_real_escape_string($db, $newName)."' WHERE zone_id = ".intval($id);
        return mysqli_query($db, $query);
    }

    public static function deleteZone($id) {
        $db = Database::getInstance()->getConnection();
        $query = "DELETE FROM zones WHERE zone_id = ".intval($id);
        return mysqli_query($db, $query);
    }
}