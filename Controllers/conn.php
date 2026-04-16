<?php


$conn = new mysqli("localhost", "root", "", "tool_library");

if ($conn->connect_error) {
    die("no connection: " . $conn->connect_error);
}
?>
