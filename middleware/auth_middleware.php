<?php
require_once __DIR__ . '/../helper/jwt_helper.php';
require_once __DIR__ . '/../helper/generalValidationMessage.php';

function checkAuth($requiredRole = null) {
    if (!isset($_COOKIE['auth_token'])) {
        setValidation("error", "Please login to access this page.");
        header("Location: /public/login.php");
        exit();
    }

    $decoded = JwtHelper::verifyToken($_COOKIE['auth_token']);

    if (!$decoded) {
        setcookie("auth_token", "", time() - 3600, "/");
        setValidation("info", "Your session has expired. Please login again.");
        header("Location: /public/login.php");
        exit();
    }

    // Role check (matches the nested 'data' from the helper fix above)
    if ($requiredRole && (!isset($decoded->data->role) || $decoded->data->role !== $requiredRole)) {
        setValidation("error", "You do not have permission to view that page.");
        header("Location: /index.php"); // Redirect to a safe page instead of login to avoid confusion
        exit();
    }

    return $decoded->data; 
}