<?php
session_start();
require_once "config.php"; 

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT users.*, roles.role_name 
        FROM users 
        JOIN roles ON users.role_id = roles.id 
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['name']      = $user['first_name'] . " " . $user['last_name'];
            $_SESSION['role']      = $user['role_name'];
            $_SESSION['dept']      = $user['department'];

            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Email not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRCO1 - Document Tracking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 font-sans">
    
    <!-- Main Login Card -->
    <div class="bg-white p-10 rounded-2xl shadow-xl w-full max-w-md border border-gray-100 text-center">
        
        <!-- LARGE LOGO SECTION -->
        <div class="mb-8">
             <img src="prclogo.png" alt="PRC Logo" 
                  class="mx-auto h-48 w-48 mb-6 drop-shadow-md" 
                  style="mix-blend-mode: multiply;">
             
             <h2 class="text-3xl font-extrabold text-blue-900 tracking-tight">PRCO1 - DTS</h2>
             <p class="text-gray-500 text-sm font-medium uppercase tracking-widest mt-1">
                Document Tracking System
             </p>
             <div class="h-1 w-20 bg-blue-800 mx-auto mt-4 rounded-full"></div>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded mb-6 text-sm text-left flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6 text-left">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email" required 
                        class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 text-gray-800 placeholder-gray-300 focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition-all" 
                        placeholder="yourname@prc.gov.ph">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" required 
                        class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 text-gray-800 placeholder-gray-300 focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none transition-all" 
                        placeholder="••••••••">
                </div>
            </div>
            
            <button type="submit" 
                class="w-full py-3 rounded-lg bg-blue-900 text-white font-bold shadow-lg hover:bg-blue-800 active:transform active:scale-95 transition-all uppercase tracking-wider mt-4">
                Login to System
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100">
            <p class="text-gray-500 text-sm">
                New personnel? 
                <a href="signup.php" class="text-blue-700 font-bold hover:underline ml-1">Create Account</a>
            </p>
        </div>
    </div>

    <!-- SYSTEM FOOTER -->
    <div class="absolute bottom-6 text-gray-400 text-[10px] uppercase tracking-widest font-bold">
        Republic of the Philippines &copy; <?= date('Y') ?>
    </div>

</body>
</html>