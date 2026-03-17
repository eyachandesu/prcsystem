
<?php
require_once __DIR__ . '/../helper/generalValidationMessage.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location:/public/login.php");
    exit();
}
$sessionPath = __DIR__ . '/../sessions';
if (!file_exists($sessionPath)) {
  mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard <strong>Testing palang toh</strong></h1>
</body>
</html>