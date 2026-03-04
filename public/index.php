<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Use TWO dots if server is run from root and you visit /public/index.php -->
  <link href="../dist/output.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <title>Login - PRC Document Tracking</title>
</head>
<!-- Header-->

<!-- Body -->
  <body class="bg-slate-100 min-h-screen flex flex-col items-center justify-center font-sans p-4">

    <div class="mb-6 text-center">
      <img src="/img/prclogo.png" alt="PRC Logo" class="h-20 w-20 mx-auto mb-3 drop-shadow-sm">
      <h1 class="text-blue-900 font-bold text-xl uppercase tracking-tight">Document Tracking System</h1>
      <p class="text-slate-500 text-xs font-semibold uppercase tracking-widest">Region 1 - Official Portal</p>
    </div>

    <!-- Login Form -->
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
    <div class="h-1.5 bg-blue-700"></div>
      <div class="p-8">
        <h2 class="text-2xl font-bold text-slate-800 mb-6 text-center">Login</h2>

      <form action="../controllers/login_handler.php" method="POST" class="space-y-5">
        
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1 tracking-wider">Username</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
              <i class="fas fa-user text-sm"></i>
            </span>
            <input type="text" name="username" placeholder="Enter your username" required
              class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none text-sm">
          </div>
        </div>
 
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1 tracking-wider">Password</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
              <i class="fas fa-lock text-sm"></i>
            </span>
            <input type="password" name="password" placeholder="••••••••" required
              class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none text-sm">
          </div>
        </div>

        <button type="submit" 
          class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3.5 rounded-xl shadow-lg transition-all uppercase text-xs tracking-widest flex items-center justify-center gap-2">
          <span>Sign In</span>
          <i class="fas fa-arrow-right text-[10px]"></i>
        </button>
      </form>

      <div class="mt-8">
        <div class="flex items-center mb-6">
          <div class="flex-grow h-px bg-slate-200"></div>
          <span class="px-3 text-[10px] text-slate-400 font-bold uppercase tracking-widest">Create an Account</span>
          <div class="flex-grow h-px bg-slate-200"></div>
        </div>
        <!-- Link to signup.php (Assuming it is in the same public folder) -->
        <a href="signup.php" class="w-full border-2 border-blue-700 text-blue-700 block text-center font-bold py-3 rounded-xl uppercase text-xs tracking-widest">
          Sign Up
        </a>
      </div>
    </div>
  </div>
</body>
</html>