<?php
require_once "../Core/database.php";

class Zone {

    public static function add($name){
        global $conn;
        $query = "INSERT INTO zones (name) VALUES ('$name')";
        mysqli_query($conn, $query);
    }

    public static function getAll(){
        global $conn;
        $result = mysqli_query($conn, "SELECT * FROM zones");
        return $result;
    }
}