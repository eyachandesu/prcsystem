<?php
// 1. Enable Error Reporting for debugging (Remove this in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start(); 
session_start();

// 2. Fix Includes - Check if these paths are exactly correct
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../helpers/jwt_helper.php"; // Check filename: jwt_helper.php vs jwt_helpers.php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . '/../helpers/generalValidationMessage.php';

use Ramsey\Uuid\Uuid;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');
    $loginType = $_POST['login_type'] ?? 'admin';

    try {
        // Step 1: Find User
        $sql = "SELECT user_id, username, password FROM user WHERE BINARY username = ? AND user_status_id < 4";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { throw new Exception("DB Prepare failed: " . $conn->error); }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Step 2: Verify Password
            if (password_verify($password, $user["password"])) {
                
                // Step 3: Update status
                $updateStatus = $conn->prepare("UPDATE user SET user_status_id = 1 WHERE user_id = ?");
                $updateStatus->bind_param("s", $user["user_id"]);
                $updateStatus->execute();

                // Step 4: Fetch full user data
                $refresh = $conn->prepare("
                    SELECT u.user_id, u.username, up.dept_id, up.email, 
                           u.user_role_id, ur.role_name,
                           CONCAT(up.user_first_name, ' ', up.user_last_name) AS user_full_name
                    FROM user u
                    LEFT JOIN user_profile up ON up.user_id = u.user_id
                    LEFT JOIN user_role ur ON ur.user_role_id = u.user_role_id
                    WHERE u.user_id = ?;
                ");
                $refresh->bind_param("s", $user["user_id"]);
                $refresh->execute();
                $updatedUser = $refresh->get_result()->fetch_assoc();

                // Step 5: Authorization
                $isAdmin = in_array($updatedUser["role_name"], ["System Administrator", "Admin"]);
                if ($loginType === "admin" && !$isAdmin) {
                    setValidation("error", "Access Denied: Admin required.");
                    header("Location: ../public/index.php");
                    exit();
                }

                // Step 6: Generate JWT
                $payload = [
                    "user_id" => $updatedUser["user_id"],
                    "username" => $updatedUser["username"],
                    "role" => $updatedUser["role_name"],
                    "exp" => time() + 3600 // 1 hour
                ];

                $jwt = JwtHelper::generateToken($payload);

                // Step 7: SET COOKIE - Modified for Localhost compatibility
                // If you are on http://localhost, 'secure' must be false
                $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
                
                setcookie("auth_token", $jwt, [
                    "expires" => time() + 3600,
                    "path" => "/",
                    "domain" => "", // Leave empty for current domain
                    "secure" => $isSecure, 
                    "httponly" => true,
                    "samesite" => "Lax", // "Strict" can sometimes block cookies on initial redirect
                ]);

                // Step 8: Logging
                $log_id = Uuid::uuid4()->toString();
                $log_stmt = $conn->prepare("INSERT INTO user_log (user_log_id, user_id, log_message, log_level) VALUES (?, ?, ?, 'LOGIN')");
                $msg = $updatedUser['username'] . " Logged In";
                $log_stmt->bind_param('sss', $log_id, $updatedUser["user_id"], $msg);
                $log_stmt->execute();

                // Step 9: Redirect
                ob_end_clean(); // Clear buffer before redirect
                if ($isAdmin) {
                    header("Location: ../public/admin_dashboard.php");
                } else {
                    header("Location: ../public/index.php");
                }
                exit();
            }
        }

        // Credentials failed
        setValidation("error", "Incorrect Username or Password");
        header("Location: ../public/index.php");
        exit();

    } catch (Exception $e) {
        die("Fatal Error: " . $e->getMessage()); // This will stop the "stuck" page and show the error
    }
}