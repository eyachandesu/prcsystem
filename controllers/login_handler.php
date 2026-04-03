<?php
/**
 * PRC Document Tracking System - Login Handler
 * Location: /controllers/login_handler.php
 */

// 1. Enable Error Reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();

// 2. FIX: Solve Mac/XAMPP session permission issues
$sessionPath = __DIR__ . '/../sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
    chmod($sessionPath, 0777);
}
session_save_path($sessionPath);
session_start();

// 3. Fix Includes using absolute paths relative to this file
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../vendor/autoload.php";

// Check if helpers exist before including to prevent fatal errors
if (file_exists(__DIR__ . "/../helper/jwt_helper.php")) {
    require_once __DIR__ . "/../helper/jwt_helper.php";
}
if (file_exists(__DIR__ . '/../helper/generalValidationMessage.php')) {
    require_once __DIR__ . '/../helper/generalValidationMessage.php';
}

use Ramsey\Uuid\Uuid;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');
    $loginType = $_POST['login_type'] ?? '';

    try {
        /**
         * Step 1: Find User & Join Data in one query
         * This is more efficient and ensures we have the Role and Name immediately.
         */
        $sql = "SELECT 
                    u.user_id, u.username, u.password, u.user_status_id,
                    ur.role_name,
                    CONCAT(up.user_first_name, ' ', up.user_last_name) AS user_full_name
                FROM user u
                LEFT JOIN user_profile up ON up.user_id = u.user_id
                LEFT JOIN user_role ur ON ur.user_role_id = u.user_role_id
                WHERE BINARY u.username = ? AND u.user_status_id < 4 
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Step 2: Verify Password
            if (password_verify($password, $user["password"])) {

                // Step 3: Update User Status to Active (1)
                $updateStatus = $conn->prepare("UPDATE user SET user_status_id = 1 WHERE user_id = ?");
                $updateStatus->bind_param("s", $user["user_id"]);
                $updateStatus->execute();

                // Step 4: Authorization Logic (Restored missing $isAdmin)
                $isAdmin = in_array($user["role_name"], ["System Administrator", "Admin"]);

                // Step 5: Check if non-admin is trying to use admin login
                if ($loginType === "") {
                    header("Location: ../public/index.php?error=" . urlencode("Access Denied: Administrative privileges required."));
                    exit();
                }

                // Step 6: Generate JWT (Using the JwtHelper class)
                $jwt = "";
                if (class_exists('JwtHelper')) {
                    $payload = [
                        "user_id" => $user["user_id"],
                        "username" => $user["username"],
                        "role" => $user["role_name"],
                        "department" => $user["dept_name"],
                        "exp" => time() + 3600 // 1 hour expiry
                    ];
                    $jwt = JwtHelper::generateToken($payload);
                }

                // Step 7: Set Auth Cookie
                $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
                setcookie("auth_token", $jwt, [
                    "expires" => time() + 3600,
                    "path" => "/",
                    "domain" => "", 
                    "secure" => $isSecure,
                    "httponly" => true,
                    "samesite" => "Lax",
                ]);

                // Step 8: Store Session Data
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['role']      = $user['role_name'];
                $_SESSION['full_name'] = $user['user_full_name'] ?? $user['username'];

                // Step 9: Logging (Insert Audit Trail)
                if (class_exists('Ramsey\Uuid\Uuid')) {
                    $log_id = Uuid::uuid4()->toString();
                    $log_stmt = $conn->prepare("INSERT INTO user_log (user_log_id, user_id, log_message, log_level) VALUES (?, ?, ?, 'LOGIN')");
                    $msg = $user['username'] . " Logged In successfully.";
                    $log_stmt->bind_param('sss', $log_id, $user["user_id"], $msg);
                    $log_stmt->execute();
                }

                // Step 10: Final Redirect
                ob_end_clean(); // Clear buffer to prevent "Headers already sent"
                
                if ($isAdmin) {
                    header("Location: ../public/admin_dashboard.php");
                } else {
                    header("Location: ../public/user_dashboard.php");
                }
                exit();
            }
        }

        // If credentials failed or user not found
        header("Location: ../public/index.php?error=" . urlencode("Invalid username or password."));
        exit();

    } catch (Exception $e) {
        error_log($e->getMessage());
        header("Location: ../public/index.php?error=" . urlencode("System error: " . $e->getMessage()));
        exit();
    }
} else {
    // If accessed directly without POST
    header("Location: ../public/index.php");
    exit();
}



