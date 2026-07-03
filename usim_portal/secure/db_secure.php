<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "usim_grades_secure";
$port = 3307;

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Turn off emulation, use real prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // VULNERABILITY FIX: Hide raw internal system trace database messages from end-users
    error_log("Secure DB Error: " . $e->getMessage()); 
    die("A system error occurred. Please try again later.");
}
?>