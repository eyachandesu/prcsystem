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
$user = checkAuth('System Administrator'); // Change 'Admin' to 'System Administrator' if that's the exact string in your DB

// If the code reaches here, the user is authenticated.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../dist/output.css" rel="stylesheet">
    <title>Admin Dashboard</title>
</head>

<body class="bg-slate-50">

    <!-- Show success message if they just logged in -->
    <div class="max-w-4xl mx-auto mt-4">
        <?= showValidation() ?>
    </div>

    <div class="p-8">
        <h1 class="text-2xl font-bold">Admin Dashboard</h1>
        <p class="text-gray-600">Welcome back, <span
                class="font-bold text-blue-600"><?= htmlspecialchars($user->full_name) ?></span>!</p>

        <div class="mt-6 p-6 bg-white rounded-xl shadow-sm border border-slate-200">
            <h2 class="font-semibold mb-2">User Info (Decoded from JWT):</h2>
            <pre class="bg-slate-100 p-4 rounded text-xs"><?php print_r($user); ?></pre>
        </div>

        <a href="../controllers/logout_handler.php" class="text-red-500 hover:text-red-700 font-medium">
            <i class="fa-solid fa-right-from-bracket mr-2"></i>Logout
        </a>
    </div>
</body>

</html>