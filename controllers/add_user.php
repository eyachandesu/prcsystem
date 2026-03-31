<?php
//debugging shit
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$sessionPath = __DIR__ . '/../sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
session_start();

require_once __DIR__ . "/../init.php";

use Ramsey\Uuid\Uuid;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $userId = Uuid::uuid4()->toString();
        
        // Sanitize inputs
        $username = trim($_POST['username'] ?? '');
        $password = password_hash(trim($_POST['password'] ?? ''), PASSWORD_BCRYPT);
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $firstName = trim($_POST['first_name'] ?? '');
        $middleName = trim($_POST['middle_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $roleId = (int)($_POST['user_role'] ?? 0);
        $deptId = (int)($_POST['department'] ?? 0);
        $birthDate = $_POST['birthday'] ?? null;

        $uploadDir = __DIR__ . "/../public/img/prof_pic/";
        $dbImageName = "default.png"; 

        // Ensure directory exists separately from file check
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Check for file upload (Moved outside the mkdir block)
        if (isset($_FILES["user_prof_pic"]) && $_FILES["user_prof_pic"]["error"] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES["user_prof_pic"]["tmp_name"];
            $fileName = $_FILES["user_prof_pic"]["name"];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ["jpg", "jpeg", "png", "gif"];

            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = uniqid() . "." . $fileExtension;
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $dbImageName = $newFileName;
                }
            }
        }

        $conn->begin_transaction();

        // Added user_status_id (assuming 1 is 'Active')
        $stmt1 = $conn->prepare("INSERT INTO user (user_id, username, password, user_role_id, user_status_id, user_created_at) VALUES (?, ?, ?, ?, 1, NOW())");
        $stmt1->bind_param("sssi", $userId, $username, $password, $roleId);
        $stmt1->execute();
        $stmt1->close();

        // Added closing parenthesis to VALUES
        $stmt2 = $conn->prepare("INSERT INTO user_profile (user_id, dept_id, user_first_name, user_middle_name, user_last_name, user_birthdate, email, user_prof) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("sissssss", $userId, $deptId, $firstName, $middleName, $lastName, $birthDate, $email, $dbImageName);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();
        setValidation("success", "User registered successfully");
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollback();
        
        // Use full path to delete file on failure
        if ($dbImageName !== "default.png" && file_exists($uploadDir . $dbImageName)) {
            unlink($uploadDir . $dbImageName);
        }

        error_log("Registration Error: " . $e->getMessage());
        setValidation("error", "Registration Failed: " . $e->getMessage());
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}