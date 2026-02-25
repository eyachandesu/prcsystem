<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'System Administrator') {
    die("Unauthorized");
}

/**
 * Helper function to generate a UUID v4 in PHP
 * since CHAR(36) is used instead of AUTO_INCREMENT
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

$action = $_REQUEST['action'] ?? '';

if ($action === 'save') {
    $role_id = $_POST['role_id'];
    $name = $_POST['role_name'];
    $desc = $_POST['description'];
    $perms = $_POST['permissions'] ?? [];

    if (empty($role_id)) {
        // --- ADD NEW ROLE ---
        $new_id = generate_uuid();
        $stmt = $conn->prepare("INSERT INTO roles (id, role_name, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $new_id, $name, $desc);
        $stmt->execute();
        
        $role_id = $new_id; // Set this for the permission inserts below
        $msg = "Role created successfully";
    } else {
        // --- UPDATE EXISTING ROLE ---
        // Changed "ssi" to "sss" because ID is now a CHAR(36) string
        $stmt = $conn->prepare("UPDATE roles SET role_name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sss", $name, $desc, $role_id);
        $stmt->execute();
        
        // Clear old permissions (quotes around $role_id because it is a string)
        $conn->query("DELETE FROM role_permissions WHERE role_id = '$role_id'");
        $msg = "Role updated successfully";
    }

    // --- INSERT SELECTED PERMISSIONS ---
    if (!empty($perms)) {
        // Changed "ii" to "ss" because both IDs are CHAR(36) strings
        $stmt_p = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($perms as $p_id) {
            $stmt_p->bind_param("ss", $role_id, $p_id);
            $stmt_p->execute();
        }
    }

    header("Location: admin_roles.php?msg=" . urlencode($msg));

} elseif ($action === 'delete') {
    $role_id = $_GET['id'];
    
    // Safety: prevent deleting System Admin (using the UUID from your SQL dump)
    $sys_admin_uuid = '550e8400-e29b-41d4-a716-446655440000';
    if ($role_id === $sys_admin_uuid) {
        header("Location: admin_roles.php?msg=" . urlencode("Cannot delete System Admin"));
        exit();
    }

    // Wrap $role_id in single quotes in the query
    $conn->query("DELETE FROM roles WHERE id = '$role_id'");
    header("Location: admin_roles.php?msg=" . urlencode("Role deleted successfully"));
}