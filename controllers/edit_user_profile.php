<?php
/**
 * PRC Document Tracking System - Edit User Handler
 * Location: /controllers/edit_user_handler.php
 */

require_once __DIR__ . "/../init.php";
ob_start();

// Enable strict error reporting for database debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Basic Input Extraction
    $user_id = $_POST["user_id"] ?? null;

    if (!$user_id) {
        $_SESSION['error'] = "User ID is required.";
        header("Location: ../public/user_list.php");
        exit();
    }

    // Define image paths
    $imageDirectory = __DIR__ . "/../public/img/prof_pic/";
    $targetFileName = null;

    try {
        /**
         * STEP 1: Fetch Current Data
         * We need this to "fill in the blanks" if the user doesn't update a field.
         */
        $fetchSql = "SELECT u.username, u.user_role_id, 
                            up.user_first_name, up.user_middle_name, up.user_last_name, 
                            up.user_birthdate, up.email, up.user_prof, up.dept_id 
                     FROM user u
                     LEFT JOIN user_profile up ON u.user_id = up.user_id
                     WHERE u.user_id = ? LIMIT 1";
        
        $stmt = $conn->prepare($fetchSql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $currentData = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$currentData) {
            throw new Exception("User record not found.");
        }

        /**
         * STEP 2: Safe Logic - Check if POST is empty, otherwise use DB value
         * This prevents overwriting with blank strings.
         */
        $username   = !empty(trim($_POST["username"]))   ? trim($_POST["username"])   : $currentData['username'];
        $role       = !empty($_POST["user_role"])        ? $_POST["user_role"]        : $currentData['user_role_id'];
        $firstName  = !empty(trim($_POST["first_name"]))  ? trim($_POST["first_name"])  : $currentData['user_first_name'];
        $middleName = !empty(trim($_POST["middle_name"])) ? trim($_POST["middle_name"]) : $currentData['user_middle_name'];
        $lastName   = !empty(trim($_POST["last_name"]))   ? trim($_POST["last_name"])   : $currentData['user_last_name'];
        $birthDate  = !empty($_POST["birth_date"])        ? $_POST["birth_date"]        : $currentData['user_birthdate'];
        $email      = !empty(trim($_POST["email"]))       ? trim($_POST["email"])       : $currentData['email'];
        $department = !empty($_POST["department"])        ? $_POST["department"]        : $currentData['dept_id'];

        /**
         * STEP 3: Handle Image Upload
         */
        $oldImageName = $currentData['user_prof'] ?? 'default.png';

        if (isset($_FILES["user_image"]) && $_FILES["user_image"]["error"] === 0) {
            $ext = strtolower(pathinfo($_FILES["user_image"]["name"], PATHINFO_EXTENSION));
            $allowedTypes = ["jpg", "jpeg", "png", "gif", "webp"];

            if (!in_array($ext, $allowedTypes)) {
                throw new Exception("Invalid image type. Allowed: " . implode(", ", $allowedTypes));
            }

            // Create unique name to prevent cache issues
            $uniqueName = uniqid('prof_') . "." . $ext;
            $newFilePath = $imageDirectory . $uniqueName;

            if (move_uploaded_file($_FILES["user_image"]["tmp_name"], $newFilePath)) {
                $targetFileName = $uniqueName;

                // Delete old image if it's not the default one
                if ($oldImageName !== "default.png" && $oldImageName !== "default.jpg") {
                    $oldFullFile = $imageDirectory . $oldImageName;
                    if (file_exists($oldFullFile)) {
                        unlink($oldFullFile);
                    }
                }
            } else {
                throw new Exception("Failed to upload image.");
            }
        } else {
            // No new image uploaded, keep the old one
            $targetFileName = $oldImageName;
        }

        /**
         * STEP 4: Database Transaction
         */
        $conn->begin_transaction();

        // 4.1 Update User table
        $stmt1 = $conn->prepare("UPDATE user SET username = ?, user_role_id = ? WHERE user_id = ?");
        $stmt1->bind_param("sis", $username, $role, $user_id);
        $stmt1->execute();
        $stmt1->close();

        // 4.2 Update User Profile table
        $stmt2 = $conn->prepare("UPDATE user_profile 
                                 SET user_first_name = ?, 
                                     user_middle_name = ?, 
                                     user_last_name = ?, 
                                     user_birthdate = ?, 
                                     email = ?, 
                                     user_prof = ?,
                                     dept_id = ? 
                                 WHERE user_id = ?");

        $stmt2->bind_param(
            "ssssssis",
            $firstName,
            $middleName,
            $lastName,
            $birthDate,
            $email,
            $targetFileName,
            $department,
            $user_id
        );
        $stmt2->execute();
        $stmt2->close();

        // Commit all changes
        $conn->commit();
        $_SESSION['success'] = "User profile updated successfully!";

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollback();
        
        error_log("Edit User Error: " . $e->getMessage());
        $_SESSION['error'] = "Update failed: " . $e->getMessage();
    }

    // Redirect back to the profile page or user list
    $redirect = $_SERVER["HTTP_REFERER"] ?? "../public/user_list.php";
    header("Location: " . $redirect);
    exit();
} else {
    header("Location: ../public/index.php");
    exit();
}