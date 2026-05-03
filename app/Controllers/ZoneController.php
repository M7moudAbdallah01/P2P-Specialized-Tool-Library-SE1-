<?php
require_once "../Models/Zone.php";

if(isset($_POST['zone'])){
    $zone = $_POST['zone'];

    Zone::add($zone);

    header("Location: ../Views/Admin/zones.php");
}