<?php
// 1. Include the middleware and helpers
require_once __DIR__ . '/../middleware/auth_middleware.php';
require_once __DIR__ . '/../helper/generalValidationMessage.php';

/**
 * 2. Use checkAuth() instead of session_start()
 * This function will:
 *  - Check if the JWT cookie exists
 *  - Verify if the JWT is valid
 *  - Check if the user has the 'Admin' role
 *  - Redirect to login.php automatically if any of the above fails
 */
$user = checkAuth('Admin'); // Change 'Admin' to 'System Administrator' if that's the exact string in your DB

// If the code reaches here, the user is authenticated.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Path fixed: assuming you are in public/ folder -->
    <link href="dist/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Dashboard | PRC DTS</title>
</head>

<body class="bg-slate-50 flex min-h-screen font-sans">

    <!-- SIDEBAR (Consistent with Transfer Page) -->
    <aside class="w-72 bg-white border-r border-slate-200 flex flex-col hidden md:flex">
        <div class="p-8 border-b border-slate-50 text-center">
            <img src="img/prclogo.png" class="h-16 w-16 mx-auto mb-4" style="mix-blend-mode: multiply;">
            <p class="text-blue-900 font-black text-sm uppercase tracking-tighter">PRC Administration</p>
        </div>

        <nav class="flex-1 p-6 space-y-3">
            <a href="admin_dashboard.php" class="flex items-center p-3 bg-blue-50 text-blue-900 rounded-xl font-bold shadow-sm">
                <i class="fas fa-chart-line w-6 text-center mr-3"></i>
                <span>Overview</span>
            </a>
            <a href="tracking.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-search w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Document Tracking</span>
            </a>
            <a href="transfer.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-exchange-alt w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Transfer Document</span>
            </a>
            <a href="receive.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-file-import w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Receive Document</span>
            </a>
        </nav>

        <div class="p-6 border-t border-slate-50">
            <a href="../controllers/logout_handler.php" class="flex items-center p-3 text-red-500 hover:bg-red-50 rounded-xl font-bold transition-all">
                <i class="fas fa-right-from-bracket w-6 text-center mr-3"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200 h-20 flex items-center justify-between px-10">
            <div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest">Administrative Portal</h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-800 leading-none"><?= htmlspecialchars($user->full_name) ?></p>
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-tighter mt-1"><?= htmlspecialchars($user->role) ?></p>
                </div>
                <div class="h-10 w-10 bg-slate-100 rounded-full flex items-center justify-center border border-slate-200">
                    <i class="fas fa-user text-slate-400"></i>
                </div>
            </div>
        </header>

        <div class="p-10">
            <!-- Alert Messages -->
            <div class="mb-8">
                <?= showValidation() ?>
            </div>

            <!-- Dashboard Welcome Header -->
            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">System Dashboard</h1>
                <p class="text-slate-500">Welcome back to the Region 1 Document Tracking System.</p>
            </div>

            <!-- Quick Stats Grid (Placeholders for now) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Documents</p>
                        <p class="text-4xl font-black text-slate-900 mt-2">124</p>
                    </div>
                    <div class="bg-blue-50 text-blue-600 p-3 rounded-2xl"><i class="fas fa-file-alt text-xl"></i></div>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pending Routing</p>
                        <p class="text-4xl font-black text-orange-500 mt-2">12</p>
                    </div>
                    <div class="bg-orange-50 text-orange-600 p-3 rounded-2xl"><i class="fas fa-clock text-xl"></i></div>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Users</p>
                        <p class="text-4xl font-black text-green-600 mt-2">8</p>
                    </div>
                    <div class="bg-green-50 text-green-600 p-3 rounded-2xl"><i class="fas fa-users text-xl"></i></div>
                </div>
            </div>

            <!-- JWT / Debug Info Card -->
            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="bg-slate-50 p-4 border-b border-slate-100 flex justify-between items-center px-8">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Session Security Context</span>
                    <span class="text-[9px] font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase">JWT Encoded</span>
                </div>
                <div class="p-8">
                    <p class="text-sm text-slate-500 mb-6 italic">The following data is decoded from your current secure authentication token:</p>
                    <div class="bg-slate-900 rounded-2xl p-6 overflow-x-auto">
                        <pre class="text-green-400 font-mono text-xs leading-relaxed"><?php print_r($user); ?></pre>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>