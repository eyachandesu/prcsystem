<?php
// FIX: Solve the XAMPP Mac permission issue before starting the session
$sessionPath = __DIR__ . '/../sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
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
    // 1. Capture and Sanitize Inputs
    $fname    = trim($_POST['first_name']);
    $lname    = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $bdate    = $_POST['birthdate'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $dept_id  = (int)$_POST['dept_id'];
    $role_id  = (int)$_POST['role_id'];
    $uuid     = generate_uuid();

    // 2. Start Database Transaction
    $conn->begin_transaction();

    try {
        // A. Check if username exists
        $checkUser = $conn->prepare("SELECT username FROM user WHERE username = ?");
        $checkUser->bind_param("s", $username);
        $checkUser->execute();
        if ($checkUser->get_result()->num_rows > 0) {
            throw new Exception("Username is already taken.");
        }

        // B. Check if email exists (Crucial because email is UNIQUE in your DB)
        $checkEmail = $conn->prepare("SELECT email FROM user_profile WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        if ($checkEmail->get_result()->num_rows > 0) {
            throw new Exception("Email address is already registered.");
        }

        // C. Insert into 'user' table
        // user_status_id = 1 (Active)
        $stmt1 = $conn->prepare("INSERT INTO user (user_id, username, password, user_role_id, user_status_id) VALUES (?, ?, ?, ?, 1)");
        $stmt1->bind_param("sssi", $uuid, $username, $password, $role_id);
        
        if (!$stmt1->execute()) {
            throw new Exception("Failed to create user account.");
        }

        // D. Insert into 'user_profile' table
        $stmt2 = $conn->prepare("INSERT INTO user_profile (user_id, dept_id, email, user_first_name, user_last_name, user_birthdate) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("sissss", $uuid, $dept_id, $email, $fname, $lname, $bdate);
        
        if (!$stmt2->execute()) {
            throw new Exception("Failed to create user profile.");
        }

        // 3. Commit changes
        $conn->commit();
        
        // Redirect to login with success message
        header("Location: ../public/index.php?success=" . urlencode("Account created successfully. Please login."));
        exit();

    } catch (Exception $e) {
        // Rollback if anything fails to prevent partial data
        $conn->rollback();
        header("Location: ../public/signup.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // If someone tries to access this file directly without POST
    header("Location: ../public/signup.php");
    exit();
}