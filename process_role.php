<?php
session_start();
include 'config.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'System Administrator') {
    die("Access Denied.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role_id = $_POST['role_id'];
    $role_name = $_POST['role_name'];
    $description = $_POST['description'];
    $selected_permissions = $_POST['permissions'] ?? [];

    $conn->begin_transaction();
 
    try {
        if (!empty($role_id)) {
            // --- UPDATE MODE ---
            $stmt = $conn->prepare("UPDATE roles SET role_name = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $role_name, $description, $role_id);
            $stmt->execute();
        } else {
            // --- ADD MODE ---
            $stmt = $conn->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $role_name, $description);
            $stmt->execute();
            $role_id = $conn->insert_id; // Get the ID of the newly created role
        }

        // --- MANAGE PERMISSIONS ---
        // 1. Delete existing associations for this role
        $del = $conn->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $del->bind_param("i", $role_id);
        $del->execute();

        // 2. Insert new ones
        if (!empty($selected_permissions)) {
            $ins = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($selected_permissions as $p_id) {
                $ins->bind_param("ii", $role_id, $p_id);
                $ins->execute();
            }
        }

        $conn->commit();
        $msg = (!empty($_POST['role_id'])) ? "Role updated successfully." : "New role created successfully.";
        header("Location: admin_roles.php?msg=" . urlencode($msg));

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: admin_roles.php?error=" . urlencode($e->getMessage()));
    }
}


?>
