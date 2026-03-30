<?php
require_once __DIR__ . "/helper/jwt_helper.php";

if (isset($_COOKIE['auth_token'])) {
    $decoded = JwtHelper::verifyToken($_COOKIE['auth_token']);
    
    // Adjust this based on if your payload has a 'data' wrapper or not
    $role = $decoded->role ?? $decoded->data->role ?? null;

    if ($role) {
        // Use an array to catch all "Admin-level" roles
        $adminRoles = ['Admin', 'System Administrator'];

        if (in_array($role, $adminRoles)) {
            header("Location: /public/admin_dashboard.php");
        } else {
            header("Location: /public/user_dashboard.php");
        }
        exit();
    }
}

// Default fallback
header("Location: /public/login.php");
exit();