<?php
date_default_timezone_set('Asia/Manila');
$host = "localhost";
$db = "prcsystem_db";
$db_user = "root";
$db_password = "12345";

// Establish database connection
try {
    $conn = new mysqli($host, $db_user, $db_password, $db);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    if (!$conn->query("SET time_zone = '+08:00'")) {
        throw new Exception("Error setting database timezone: " . $conn->error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}