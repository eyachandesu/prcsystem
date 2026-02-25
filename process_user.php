<?php
session_start();
include 'config.php';

// Security: Ensure only Admin can access this script
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'System Administrator') {
    die("Unauthorized");
}

/**
 * Helper function to generate a UUID v4
 */
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id    = $_POST['user_id'];
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $dept       = $_POST['department'];
    $role_id    = $_POST['role_id']; // This is a UUID string
    $password   = $_POST['password'];

    if (!empty($user_id)) {
        // --- UPDATE EXISTING USER ---
        if (!empty($password)) {
            // Update including password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, department=?, role_id=?, password=? WHERE id=?");
            // All parameters are strings ("sssssss")
            $stmt->bind_param("sssssss", $first_name, $last_name, $email, $dept, $role_id, $hashed, $user_id);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, department=?, role_id=? WHERE id=?");
            // All parameters are strings ("ssssss")
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $dept, $role_id, $user_id);
        }
        $msg = "User details updated successfully.";
    } else {
        // --- CREATE NEW USER ---
        $new_id = generate_uuid();
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Include the 'id' column in the insert
        $stmt = $conn->prepare("INSERT INTO users (id, first_name, last_name, email, password, role_id, department, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
        
        // All parameters are strings ("sssssss")
        $stmt->bind_param("sssssss", $new_id, $first_name, $last_name, $email, $hashed, $role_id, $dept);
        $msg = "New user account created.";
    }

    if ($stmt->execute()) {
        header("Location: admin_users.php?msg=" . urlencode($msg));
    } else {
        // Error handling for duplicate emails or SQL issues
        $error_msg = ($conn->errno == 1062) ? "Error: Email address already exists." : $conn->error;
        header("Location: admin_users.php?error=" . urlencode($error_msg));
    }
    exit();
}
?>