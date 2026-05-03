<?php
require_once __DIR__ . "/../../Core/database.php";

class Zone {
    public static function add($name){
        $db = Database::getInstance()->getConnection();
        $safe_name = mysqli_real_escape_string($db, $name);
        $query = "INSERT INTO zones (name) VALUES ('$safe_name')";
        return mysqli_query($db, $query);
    }

    public static function getAll(){
        $db = Database::getInstance()->getConnection();
        $query = "SELECT * FROM zones";
        try {
            $result = mysqli_query($db, $query);
            if ($result) {
                return mysqli_fetch_all($result, MYSQLI_ASSOC);
            }
        } catch (Exception $e) {
            return [];
        }
        return [];
    }
}