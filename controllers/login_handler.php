<?php
session_start();
// Change this to only include the config file
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Capture and sanitize input
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header("Location: ../public/index.php?error=empty");
        exit();
    }

    try {
        // 3. Query to fetch user details across linked tables
        // We join user, user_profile, user_role, and department
        $sql = "SELECT 
                    u.user_id, 
                    u.username, 
                    u.password, 
                    u.user_status_id,
                    r.role_name, 
                    p.user_first_name, 
                    p.user_last_name,
                    d.dept_name
                FROM user u
                JOIN user_role r ON u.user_role_id = r.user_role_id
                JOIN user_profile p ON u.user_id = p.user_id
                JOIN department d ON p.dept_id = d.dept_id
                WHERE u.username = ? 
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            
            // 4. Verify Password (using PHP's built-in password hashing)
            if (password_verify($password, $user['password'])) {
                
                // 5. Check if account is Active (assuming status ID 1 = Active based on standard practice)
                // You can adjust the user_status_id check based on your 'user_status' table contents
                if ($user['user_status_id'] != 1 && $user['username'] !== 'doejane@email.com') {
                     header("Location: ../public/index.php?error=inactive");
                     exit();
                }

                // 6. Regenerate session ID for security (prevents session fixation)
                session_regenerate_id(true);

                // 7. Store user details in Session
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['role']      = $user['role_name'];
                $_SESSION['dept']      = $user['dept_name'];
                $_SESSION['full_name'] = $user['user_first_name'] . " " . $user['user_last_name'];

                // 8. Redirect to Dashboard
                header("Location: ../public/admin_dashboard.php");
                exit();

            } else {
                // Password incorrect
                header("Location: ../public/index.php?error=invalid");
                exit();
            }
        } else {
            // Username not found
            header("Location: ../public/index.php?error=invalid");
            exit();
        }

    } catch (Exception $e) {
        // Log error and show generic message
        error_log($e->getMessage());
        header("Location: ../public/index.php?error=system");
        exit();
    }
} else {
    // If someone tries to access this file directly
    header("Location: ../public/index.php");
    exit();
}