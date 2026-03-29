<?php
// 1. Include the middleware and helpers
require_once __DIR__ . '/../middleware/auth_middleware.php';
require_once __DIR__ . '/../helper/generalValidationMessage.php';

/**
 * 2. Use checkAuth() instead of session_start()
 * This function will:
 *  - Check if the JWT cookie exists
 *  - Verify if the JWT is valid
 *  - Check if the user has the 'Admin' role
 *  - Redirect to login.php automatically if any of the above fails
 */
$user = checkAuth('User'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>hello world</h1>
    <a href="../controllers/logout_handler.php">logout</a>
</body>
</html>