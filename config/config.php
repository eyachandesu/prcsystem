<?php
// config/config.php

// We are bypassing vendor/autoload.php and .env for now 
// to fix your 500 error immediately.

$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Leave empty for XAMPP default
$db_name = 'prcsystem_db'; // Ensure this matches phpMyAdmin exactly

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>