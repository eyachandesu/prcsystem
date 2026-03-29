<?php
ob_start();
session_start(); // Start session AT THE TOP so setValidation works

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../helper/jwt_helper.php";
require_once __DIR__ . "/../helper/generalValidationMessage.php";
require_once __DIR__ . "/../vendor/autoload.php";

use Ramsey\Uuid\Uuid;

$userId = null;
$username = "Unknown User";

if (isset($_COOKIE['auth_token'])) {
    $decoded = JwtHelper::verifyToken($_COOKIE['auth_token']);
    if ($decoded && isset($decoded->data)) {
        $userId = $decoded->data->user_id;
        $username = $decoded->data->username;
    }
}

if ($userId) {
    try {
        $log_id = Uuid::uuid4()->toString();
        $log_stmt = $conn->prepare("INSERT INTO user_log (user_log_id, user_id, log_message, log_level) VALUES (?, ?, ?, 'LOGOUT')");
        $msg = $username . " Logged Out";
        $log_stmt->bind_param('sss', $log_id, $userId, $msg);
        $log_stmt->execute();
        
        // FIX 1: Use '2' instead of '0' to avoid the Foreign Key Error
        $updateStatus = $conn->prepare("UPDATE user SET user_status_id = 2 WHERE user_id = ?");
        $updateStatus->bind_param("s", $userId);
        $updateStatus->execute();

    } catch (Exception $e) {
        error_log("Logout Logging Error: " . $e->getMessage());
    }
}

// FIX 2: Clear cookie first
setcookie("auth_token", "", time() - 3600, "/");

// FIX 3: Set validation message BEFORE destroying everything else
setValidation("success", "You have been logged out successfully.");

// NOTE: We don't call session_destroy() here if we want the validation message 
// to survive the redirect. Instead, we just clear the USER data from the session.
unset($_SESSION['user_id']); 
// session_destroy(); <-- REMOVED so your success message works!

header("Location: /public/login.php");
exit();