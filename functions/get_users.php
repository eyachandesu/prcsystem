<?php
require_once __DIR__ . "/../init.php"; 
$userData = checkAuth(); 
$current_user_id = $userData->user_id;


if (isset($_GET['dept_id'])) {
    $deptId = (int)$_GET['dept_id'];

    $users = fetchUsersByDepartment($conn, $deptId, $current_user_id);
    echo json_encode($users);
} else {
    echo json_encode([]);
}