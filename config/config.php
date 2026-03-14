<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "prcsystem_db"; // Updated to your new DB name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper to generate UUIDs in PHP if needed, 
// though we will use MySQL's UUID() function in queries.
?>