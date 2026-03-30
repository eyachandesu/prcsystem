<?php
require_once __DIR__ . '/../helper/jwt_helper.php';
require_once __DIR__ . '/../helper/generalValidationMessage.php';

// Check if a valid token already exists
if (isset($_COOKIE['auth_token']) && !isset($_GET['error'])) {
    $decoded = JwtHelper::verifyToken($_COOKIE['auth_token']);
    
    // Check for $decoded->data->role (The UUID doesn't affect this part)
    if ($decoded && isset($decoded->data->role)) {
        if (trim($decoded->data->role) === 'Admin') {
            header("Location: /public/admin_dashboard.php");
            exit();
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../dist/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <title>Login - PRCRO1 Document Tracking</title>
</head>

<body class="bg-slate-100 min-h-screen flex flex-col items-center justify-center font-sans p-4">

  <div class="mb-6 text-center">
    <img src="img/prclogo.png" alt="PRC Logo" class="h-20 w-20 mx-auto mb-3 drop-shadow-sm">
    <h1 class="text-blue-900 font-bold text-xl uppercase tracking-tight">Document Tracking System</h1>
    <p class="text-slate-500 text-xs font-semibold uppercase tracking-widest">Region 1 - Official Portal</p>
  </div>

  <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
    <div class="h-1.5 bg-blue-700"></div>
    <div class="p-8">
      <h2 class="text-2xl font-bold text-slate-800 mb-6 text-center">Login</h2>

      <!-- Display Validation Messages from Helper -->
      <div class="mb-4">
        <?= showValidation() ?>
      </div>

      <form action="../controllers/login_handler.php" method="POST" class="space-y-6">
        <input type="hidden" name="login_type"">
        
        <div class="space-y-1.5">
          <label class="text-sm font-semibold text-gray-700">Username</label>
          <div class="relative">
             <i class="fa-solid fa-user absolute left-3 top-4 text-gray-400"></i>
            <input type="text" name="username" required
              class="w-full pl-10 pr-4 py-3 rounded-lg bg-white border border-gray-200 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all duration-200"
              placeholder="Enter your username">
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-semibold text-gray-700">Password</label>
          <div class="relative">
            <i class="fa-solid fa-lock absolute left-3 top-4 text-gray-400"></i>
            <input type="password" name="password" required
              class="w-full pl-10 pr-4 py-3 rounded-lg bg-white border border-gray-200 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all duration-200"
              placeholder="••••••••">
          </div>
        </div>

        <button type="submit"
          class="w-full py-3.5 rounded-lg bg-[#413072] hover:bg-[#34265b] text-black font-semibold shadow-lg active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2">
          Sign In
        </button>
      </form>
    </div>
  </div>
</body>
</html>