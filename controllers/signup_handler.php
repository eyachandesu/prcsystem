<?php
session_start();
require_once '../config/config.php';

// Helper function to generate UUID v4
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Capture Inputs
    $fname    = trim($_POST['first_name']);
    $lname    = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $bdate    = $_POST['birthdate'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $dept_id  = $_POST['dept_id'];
    $role_id  = $_POST['role_id'];
    $uuid     = generate_uuid();

    // 2. Start Database Transaction
    $conn->begin_transaction();

    try {
        // A. Check if username exists
        $check = $conn->prepare("SELECT username FROM user WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            throw new Exception("Username already taken.");
        }

        // B. Insert into 'user' table
        // user_status_id = 1 (Assuming 1 is Active)
        $stmt1 = $conn->prepare("INSERT INTO user (user_id, username, password, user_role_id, user_status_id) VALUES (?, ?, ?, ?, 1)");
        $stmt1->bind_param("sssi", $uuid, $username, $password, $role_id);
        $stmt1->execute();

        // C. Insert into 'user_profile' table
        $stmt2 = $conn->prepare("INSERT INTO user_profile (user_id, dept_id, email, user_first_name, user_last_name, user_birthdate) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("sissss", $uuid, $dept_id, $email, $fname, $lname, $bdate);
        $stmt2->execute();

        // 3. Commit changes
        $conn->commit();
        header("Location: ../public/index.php?success=registered");
        exit();

    } catch (Exception $e) {
        // Rollback if anything fails
        $conn->rollback();
        header("Location: ../public/signup.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}