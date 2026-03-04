<?php
session_start();
require_once "../config/database.php";

if (isset($_POST['add_user'])) {
    $user_id = generate_uuid();
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = $_POST['user_role_id'];
    $status_id = $_POST['user_status_id'];

    $conn->begin_transaction();
    try {
        // Table: user
        $stmt1 = $conn->prepare("INSERT INTO user (user_id, username, password, user_role_id, user_status_id) VALUES (?, ?, ?, ?, ?)");
        $stmt1->bind_param("sssii", $user_id, $username, $password, $role_id, $status_id);
        $stmt1->execute();

        // Table: user_profile
        $stmt2 = $conn->prepare("INSERT INTO user_profile (user_id, dept_id, email, user_first_name, user_last_name, user_birthdate) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("sissss", $user_id, $_POST['dept_id'], $_POST['email'], $_POST['fname'], $_POST['lname'], $_POST['bday']);
        $stmt2->execute();

        $conn->commit();
        header("Location: ../public/admin_users.php?msg=User Created");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../public/admin_users.php?error=" . $e->getMessage());
    }
}
?>