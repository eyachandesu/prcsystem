<?php
session_start();
ob_clean();
// Config and autoload
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/vendor/autoload.php";

// Middleware
require_once __DIR__ . "/middleware/auth_middleware.php";

// Helpers
require_once __DIR__ . "/helper/generalValidationMessage.php";
require_once __DIR__ . "/helper/toast.php";
require_once __DIR__ . "/helper/jwt_helper.php";


//Functions
require_once __DIR__ . "/functions/user_visibility.php";
require_once __DIR__ . "/functions/fetch_user_role.php";
require_once __DIR__ . "/functions/fetch_department.php";
require_once __DIR__ . "/functions/role_functions.php";
require_once __DIR__ . "/functions/document_visibility.php";
require_once __DIR__ . "/functions/fetch_users_department.php";
require_once __DIR__ . "/functions/get_users.php";
?>