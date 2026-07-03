<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "usim_grades_insecure";
$port = 3307;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Insecure Database Connection failed: " . $conn->connect_error);
}
?>