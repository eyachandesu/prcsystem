<?php
// config/config.php

$host = "127.0.0.1";
$username = "root";
$password = ""; // ENSURE THIS IS EMPTY
$database = "prcsystem_db";

// Use a try-catch block to handle connection errors gracefully
try {
    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (mysqli_sql_exception $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>