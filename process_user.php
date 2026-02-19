<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id    = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $dept       = $_POST['department'];
    $role_id    = $_POST['role_id'];
    $password   = $_POST['password'];

    if (!empty($user_id)) {
        // --- UPDATE EXISTING USER ---
        if (!empty($password)) {
            // Update including password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, department=?, role_id=?, password=? WHERE id=?");
            $stmt->bind_param("ssssisi", $first_name, $last_name, $email, $dept, $role_id, $hashed, $user_id);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, department=?, role_id=? WHERE id=?");
            $stmt->bind_param("ssssii", $first_name, $last_name, $email, $dept, $role_id, $user_id);
        }
        $msg = "User details updated successfully.";
    } else {
        // --- CREATE NEW USER ---
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role_id, department, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("ssssis", $first_name, $last_name, $email, $hashed, $role_id, $dept);
        $msg = "New user account created.";
    }

    if ($stmt->execute()) {
        header("Location: admin_users.php?msg=" . $msg);
    } else {
        header("Location: admin_users.php?error=" . $conn->error);
    }
}
?>