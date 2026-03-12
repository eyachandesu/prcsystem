<?php
// FIX 1: Solve the Mac/XAMPP session permission issue
$sessionPath = __DIR__ . '/../sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
    chmod($sessionPath, 0777);
}
session_save_path($sessionPath);
session_start();

require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Capture and sanitize input
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 2. Basic Validation
    if (empty($username) || empty($password)) {
        header("Location: ../public/index.php?error=" . urlencode("Please enter both username and password."));
        exit();
    }

    try {
        // 3. Query based on your exact database schema
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
            
            // 4. Verify Password
            if (password_verify($password, $user['password'])) {
                
                // 5. Check if account is Active (Status ID 1 = Active in your DB)
                if ((int)$user['user_status_id'] !== 1) {
                     header("Location: ../public/index.php?error=" . urlencode("Your account is inactive. Please contact admin."));
                     exit();
                }

                // 6. Security: Regenerate session ID
                session_regenerate_id(true);

                // 7. Store user details in Session
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['role']      = $user['role_name'];
                $_SESSION['dept']      = $user['dept_name'];
                $_SESSION['full_name'] = $user['user_first_name'] . " " . $user['user_last_name'];

                // 8. Redirect based on Role
                // In your dashboard code, you check for 'System Administrator'
                header("Location: ../public/admin_dashboard.php");
                exit();

            } else {
                // Password incorrect
                header("Location: ../public/index.php?error=" . urlencode("Invalid username or password."));
                exit();
            }
        } else {
            // Username not found
            header("Location: ../public/index.php?error=" . urlencode("Invalid username or password."));
            exit();
        }

    } catch (Exception $e) {
        // Log error and show message
        error_log($e->getMessage());
        header("Location: ../public/index.php?error=" . urlencode("System error occurred. Please try again later."));
        exit();
    }
} else {
    header("Location: ../public/index.php");
    exit();
}