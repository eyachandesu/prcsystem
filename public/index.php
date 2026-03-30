<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../helper/jwt_helper.php';
require_once __DIR__ . '/../helper/generalValidationMessage.php';

// 1. If already logged in, skip the login page
if (isset($_COOKIE['auth_token']) && !isset($_GET['error'])) {
    $decoded = JwtHelper::verifyToken($_COOKIE['auth_token']);
    if ($decoded && isset($decoded->data->role)) {
        if (trim($decoded->data->role) === '') {
            header("Location: admin_dashboard.php");
            exit();
        }
    }
}

// 2. Capture messages
$url_error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "";
$session_msg = function_exists('showValidation') ? showValidation() : "";
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="dist/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <title>Login | PRC DTS</title>
  <style>
      .custom-msg-box svg, .custom-msg-box i, .custom-msg-box img {
          width: 32px !important;
          height: 32px !important;
          margin-bottom: 8px;
          display: block;
          margin-left: auto;
          margin-right: auto;
      }
  </style>
</head>

<body class="bg-slate-50 min-h-screen flex flex-col items-center justify-center p-4 font-sans">

  <!-- HEADER -->
  <div class="mb-8 text-center">
    <img src="img/prclogo.png" class="h-24 w-24 mx-auto mb-4 drop-shadow-md transition-transform hover:scale-105 duration-300">
    <h1 class="text-slate-900 font-black text-2xl uppercase tracking-tight">Document Tracking System</h1>
    <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em] mt-1">Region 1 - Official Portal</p>
  </div>

  <!-- LOGIN CARD -->
  <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
    <div class="h-1.5 bg-blue-600"></div>
    <div class="p-10">

      <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-slate-800">Welcome Back</h2>
        <p class="text-sm text-slate-400 mt-1">Please enter your credentials to continue</p>
      </div>

      <!-- ERROR ALERTS -->
      <?php if (!empty($url_error)): ?>
      <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-100 flex items-center gap-3">
          <i class="fas fa-exclamation-circle text-red-500"></i>
          <p class="text-red-700 text-xs font-bold uppercase tracking-tight"><?= $url_error ?></p>
      </div>
      <?php endif; ?>

      <!-- SUCCESS ALERTS -->
      <?php if (!empty(trim($session_msg)) && empty($url_error)): ?>
      <div class="mb-6 p-5 rounded-2xl bg-green-50 border border-green-200 text-center custom-msg-box">
          <div class="text-green-700 text-sm font-semibold"><?= $session_msg ?></div>
      </div>
      <?php endif; ?>

      <!-- FORM -->
      <form action="../controllers/login_handler.php" method="POST" class="space-y-5">
        <input type="hidden" name="login_type" value="Admin">
        
        <div>
          <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Username</label>
          <div class="relative group">
            <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
            <input type="text" name="username" required
              class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
              placeholder="Enter your username">
          </div>
        </div>

        <div>
          <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">Password</label>
          <div class="relative group">
            <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
            <input type="password" name="password" required
              class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
              placeholder="••••••••">
          </div>
        </div>

        <button type="submit"
          class="w-full py-4 rounded-xl bg-slate-900 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-widest shadow-lg shadow-slate-200 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
          <span>Sign In to Portal</span>
          <i class="fa-solid fa-arrow-right-long text-[10px]"></i>
        </button>
      </form>

      <div class="mt-8 text-center border-t border-slate-100 pt-6">
          <p class="text-xs text-slate-400 font-medium">
              Don't have an account? <a href="signup.php" class="text-blue-600 font-bold hover:underline">Register here</a>
          </p>
      </div>
    </div>
  </div>
</body>
</html>