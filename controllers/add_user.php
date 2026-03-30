<?php
// Enable strict error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$sessionPath = __DIR__ . '/../sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
session_start();

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . '/../helper/generalValidationMessage.php';

use Ramsey\Uuid\Uuid;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $userId = Uuid::uuid4()->toString();
        
        // 1. Capture and Sanitize inputs
        $username = trim($_POST['username'] ?? '');
        $passwordRaw = trim($_POST['password'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $firstName = trim($_POST['first_name'] ?? '');
        $middleName = trim($_POST['middle_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $roleId = (int)($_POST['user_role'] ?? 0);
        $deptId = (int)($_POST['department'] ?? 0);
        $birthDate = $_POST['birthday'] ?? '';

        // 2. VALIDATION CHECKS
        if (!$email) throw new Exception("Invalid email format.");
        if (empty($username) || empty($passwordRaw)) throw new Exception("Username and Password are required.");
        if (empty($birthDate)) throw new Exception("Birthdate is required.");

        // 3. CHECK FOR DUPLICATES (Prevents crash on UNIQUE keys)
        $checkQuery = $conn->prepare("SELECT u.username, up.email FROM user u JOIN user_profile up ON u.user_id = up.user_id WHERE u.username = ? OR up.email = ? LIMIT 1");
        $checkQuery->bind_param("ss", $username, $email);
        $checkQuery->execute();
        $duplicate = $checkQuery->get_result()->fetch_assoc();
        if ($duplicate) {
            if ($duplicate['username'] === $username) throw new Exception("Username is already taken.");
            if ($duplicate['email'] === $email) throw new Exception("Email is already registered.");
        }

        // 4. Handle Profile Picture
        $uploadDir = __DIR__ . "/../public/img/prof_pic/";
        $dbImageName = "default.png"; 

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (isset($_FILES["user_prof_pic"]) && $_FILES["user_prof_pic"]["error"] === UPLOAD_ERR_OK) {
            $fileName = $_FILES["user_prof_pic"]["name"];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ["jpg", "jpeg", "png"];

            if (in_array($fileExtension, $allowedExtensions)) {
                $dbImageName = uniqid() . "." . $fileExtension;
                move_uploaded_file($_FILES["user_prof_pic"]["tmp_name"], $uploadDir . $dbImageName);
            }
        }

        // 5. DATABASE INSERTION
        $passwordHash = password_hash($passwordRaw, PASSWORD_BCRYPT);
        $conn->begin_transaction();

        // Insert into 'user' (Assuming 1 is Active in your user_status table)
        $stmt1 = $conn->prepare("INSERT INTO user (user_id, username, password, user_role_id, user_status_id) VALUES (?, ?, ?, ?, 1)");
        $stmt1->bind_param("sssi", $userId, $username, $passwordHash, $roleId);
        $stmt1->execute();

        // Insert into 'user_profile'
        $stmt2 = $conn->prepare("INSERT INTO user_profile (user_id, dept_id, user_first_name, user_middle_name, user_last_name, user_birthdate, email, user_prof) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("sissssss", $userId, $deptId, $firstName, $middleName, $lastName, $birthDate, $email, $dbImageName);
        $stmt2->execute();

        $conn->commit();
        setValidation("success", "Personnel '$username' added successfully.");
        
        header("Location: ../public/admin_users.php");
        exit();

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollback();
        
        // Delete uploaded file if DB failed
        if (isset($dbImageName) && $dbImageName !== "default.png" && file_exists($uploadDir . $dbImageName)) {
            unlink($uploadDir . $dbImageName);
        }

        setValidation("error", $e->getMessage());
        header("Location: ../public/admin_users.php");
        exit();
    }
}