<?php
require_once __DIR__ . '/../helper/jwt_helper.php';

function checkAuth($requiredRole = null) {
    if (!isset($_COOKIE['auth_token'])) {
        header("Location: login.php?error=unauthorized");
        exit();
    }

    $decoded = JwtHelper::verifyToken($_COOKIE['auth_token']);

    if (!$decoded) {
        // Token expired or tampered
        setcookie("auth_token", "", time() - 3600, "/");
        header("Location: login.php?error=session_expired");
        exit();
    }

    // Check role if required
    if ($requiredRole && $decoded->data->role !== $requiredRole) {
        header("Location: index.php?error=forbidden");
        exit();
    }

    return $decoded->data; // Return user info for use in the page
}