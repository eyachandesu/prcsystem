<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_POST["user_id"];
    $username = $_POST["edit_username"] ?? null;
    $role = $_POST["edit_user_role"] ?? null;
    $password = $_POST["edit_password"] ?? null;

    try {
        $conn->begin_transaction();

        if (!empty($password)) {
            //  Hash password ONLY if provided
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "UPDATE user
                    SET username = ?, user_role_id = ?, password = ?
                    WHERE user_id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siss", $username, $role, $hashed_password, $user_id);

        } else {

            $sql = "UPDATE user 
                    SET username = ?, user_role_id = ?
                    WHERE user_id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sis", $username, $role, $user_id);
        }

        $stmt->execute();
        $conn->commit();

        $stmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        echo "User update error: " . $e->getMessage();
        exit;
    } finally {
        $conn->close();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}