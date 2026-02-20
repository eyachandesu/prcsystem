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

// -- Hello my name is eya nichole barcena and i am a student of pangasinan state university bayambang campus, i am currently taking up bachelor of science in information technology and i am in my 4th year.
// i am currently working on a document tracking system for my supervisor in finance and administative department, the system is designed to help them track the movement of documents within their department and
// other departments, it will also help them to managa their documents more efficiently and effectively, i am currently in the process of developing the system and i am hoping to complete it by the end of my on the job training,
// i am also hoping to learn more about web development and database management through this project, thank you for giving me the opportunity to work on this project and i am looking forward to learning more and improving my skills in the field of information technology.
// 

?>
