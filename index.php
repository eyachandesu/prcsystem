<?php
require_once __DIR__ . "/helper/jwt_helper.php";

if (isset($_COOKIE['auth_token'])) {
    $decoded = JwtHelper::verifyToken($_COOKIE['auth_token']);
    
    if ($decoded && isset($decoded->data->role)) {
        $role = $decoded->data->role;

        // Redirect based on the actual role in the token
        if ($role === 'System Administrator') {
            header("Location: /public/admin_dashboard.php");
            exit();
        } else {
            // If they are a normal user, send them to the user area (or just stay here)
             header("Location: /public/user_dashboard.php"); 
             exit();
            echo "Welcome, User. You do not have Admin access.";
            exit();
        }
    }
}

// No valid token? Go to login.
header("Location: /public/login.php");
exit();