<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dept_id = $_POST['dept_id'];
    $dept_name = trim($_POST['dept_name']);
    $dept_code = strtoupper(trim($_POST['dept_code']));

    if (!empty($dept_id)) {
        // Update Existing
        $stmt = $conn->prepare("UPDATE departments SET dept_name = ?, dept_code = ? WHERE id = ?");
        $stmt->bind_param("ssi", $dept_name, $dept_code, $dept_id);
        $stmt->execute();
        $msg = "Department updated successfully.";
    } else {
        // Create New
        $stmt = $conn->prepare("INSERT INTO departments (dept_name, dept_code, status) VALUES (?, ?, 'active')");
        $stmt->bind_param("ss", $dept_name, $dept_code);
        $stmt->execute();
        $msg = "New department created successfully.";
    }

    header("Location: admin_departments.php?msg=" . urlencode($msg));
    exit();
}