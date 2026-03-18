<?php
ob_start();
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../helper/jwt_helper.php";
require_once __DIR__ . "/../helper/generalValidationMessage.php";
require_once __DIR__ . "/../vendor/autoload.php";

use Ramsey\Uuid\Uuid;

// 1. Initialize variables for logging
$userId = null;
$username = "Unknown User";

// 2. Identify who is logging out (before we destroy the cookie)
if (isset($_COOKIE['auth_token'])) {
    $decoded = JwtHelper::verifyToken($_COOKIE['auth_token']);
    if ($decoded && isset($decoded->data)) {
        $userId = $decoded->data->user_id;
        $username = $decoded->data->username;
    }
}

// 3. Log the logout event in the database
if ($userId) {
    try {
        $log_id = Uuid::uuid4()->toString();
        $log_stmt = $conn->prepare("INSERT INTO user_log (user_log_id, user_id, log_message, log_level) VALUES (?, ?, ?, 'LOGOUT')");
        $msg = $username . " Logged Out";
        $log_stmt->bind_param('sss', $log_id, $userId, $msg);
        $log_stmt->execute();
        
        // Optional: Update user status in the 'user' table if you track online status
        $updateStatus = $conn->prepare("UPDATE user SET user_status_id = 0 WHERE user_id = ?");
        $updateStatus->bind_param("s", $userId);
        $updateStatus->execute();
    } catch (Exception $e) {
        error_log("Logout Logging Error: " . $e->getMessage());
    }
}

// 4. Clear the Auth Cookie
// We set the expiration to the past (time() - 3600) to tell the browser to delete it
setcookie("auth_token", "", time() - 3600, "/");

// 5. Clear any legacy PHP Sessions (just in case)
session_start();
session_unset();
session_destroy();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// 6. Set a success message for the login page
setValidation("success", "You have been logged out successfully.");

// 7. Redirect to login
header("Location: /public/login.php");
exit();