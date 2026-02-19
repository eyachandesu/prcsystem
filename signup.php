<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "config.php"; 

$error = "";
$success = "";

$roles_query = "SELECT id, role_name FROM roles ORDER BY id ASC";
$roles_result = $conn->query($roles_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = trim($_POST['first_name']);
    $lname = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $dept  = $_POST['department'];
    $role_id = $_POST['role_id'];
    $pass  = $_POST['password'];
    $conf_pass = $_POST['confirm_password'];

    if (empty($fname) || empty($lname) || empty($email) || empty($pass) || empty($role_id)) {
        $error = "All fields are required.";
    } elseif ($pass !== $conf_pass) {
        $error = "Passwords do not match.";
    } else {
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $result = $check_email->get_result();

        if ($result->num_rows > 0) {
            $error = "Email address already registered.";
        } else {
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
            $status = 'active';

            $sql = "INSERT INTO users (first_name, last_name, email, password, role_id, department, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("ssssiss", $fname, $lname, $email, $hashed_password, $role_id, $dept, $status);
                if ($stmt->execute()) {
                    $success = "Account successfully recorded! Redirecting to login...";
                    header("refresh:2;url=login.php");
                } else {
                    $error = "Database Error: " . $stmt->error;
                }
            } else {
                $error = "SQL Preparation Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - PRC Document Tracking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 p-4 font-sans">

    <div class="bg-white p-10 rounded-2xl shadow-xl w-full max-w-lg border border-gray-100">
        
        <!-- LARGE LOGO SECTION -->
        <div class="text-center mb-8">
             <img src="prclogo.png" alt="PRC Logo" 
                  class="mx-auto h-40 w-40 mb-4 drop-shadow-md" 
                  style="mix-blend-mode: multiply;">
             
             <h2 class="text-2xl font-extrabold text-blue-900 uppercase tracking-tight">PRC Registration</h2>
             <p class="text-gray-500 text-sm font-medium">Create your Document Tracking account</p>
             <div class="h-1 w-16 bg-blue-800 mx-auto mt-3 rounded-full"></div>
        </div>

        <!-- NOTIFICATIONS -->
        <?php if($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 mb-6 text-sm flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-3 mb-6 text-sm flex items-center">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- SIGNUP FORM -->
        <form action="signup.php" method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">First Name</label>
                    <input type="text" name="first_name" required 
                        class="w-full border border-gray-200 p-2.5 rounded-lg focus:ring-2 focus:ring-blue-600 outline-none transition-all text-sm" placeholder="Juan">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Last Name</label>
                    <input type="text" name="last_name" required 
                        class="w-full border border-gray-200 p-2.5 rounded-lg focus:ring-2 focus:ring-blue-600 outline-none transition-all text-sm" placeholder="Dela Cruz">
                </div>
            </div>
            
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">PRC Email Address</label>
                <input type="email" name="email" required 
                    class="w-full border border-gray-200 p-2.5 rounded-lg focus:ring-2 focus:ring-blue-600 outline-none transition-all text-sm" placeholder="juan.delacruz@prc.gov.ph">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Department</label>
                    <select name="department" required class="w-full border border-gray-200 p-2.5 rounded-lg bg-white text-sm outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" disabled selected>Select Dept</option>
                        <option>Licensure Office</option>
                        <option>Legal Service</option>
                        <option>ICT Service</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">System Role</label>
                    <select name="role_id" required class="w-full border border-gray-200 p-2.5 rounded-lg bg-white text-sm outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" disabled selected>Select Role</option>
                        <?php 
                        if ($roles_result->num_rows > 0) {
                            while($role = $roles_result->fetch_assoc()) {
                                echo "<option value='".$role['id']."'>".$role['role_name']."</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Password</label>
                    <input type="password" name="password" required 
                        class="w-full border border-gray-200 p-2.5 rounded-lg focus:ring-2 focus:ring-blue-600 outline-none transition-all text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Confirm</label>
                    <input type="password" name="confirm_password" required 
                        class="w-full border border-gray-200 p-2.5 rounded-lg focus:ring-2 focus:ring-blue-600 outline-none transition-all text-sm">
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-900 text-white font-bold py-3 rounded-lg hover:bg-blue-800 shadow-lg active:scale-95 transition-all mt-4 uppercase tracking-wider">
                Register Account
            </button>
            
            <div class="text-center pt-4 border-t border-gray-100 mt-4">
                <p class="text-gray-500 text-sm">Already have an account? <a href="login.php" class="text-blue-700 font-bold hover:underline">Login here</a></p>
            </div>
        </form>
    </div>

</body>
</html>