<?php
require_once __DIR__ . "/middleware/auth_middleware.php";

$cookie_name = "auth_token";

if (isset($_COOKIE[$cookie_name])) {
    // Cookie is set, proceed to the main application
    header("Location: public/admin_dashboard.php");
    exit();
} else {
    // Cookie is not set, redirect to login page
    header("Location: public/login.php");
    exit();
}
?>