<?php
session_start();
require_once "../config/config.php";
if ($_SESSION['role'] !== 'System Administrator') { header("Location: index.php"); exit(); }

// Fetch Statistics
$total_docs = $conn->query("SELECT COUNT(*) as total FROM document")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) as total FROM user")->fetch_assoc()['total'];
$total_depts = $conn->query("SELECT COUNT(*) as total FROM department")->fetch_assoc()['total'];

// Recent Transactions
$recent_logs = $conn->query("
    SELECT tl.*, d.ref_no, up.user_first_name, up.user_last_name, ds.status_name 
    FROM transaction_logs tl
    JOIN document d ON tl.doc_id = d.doc_id
    JOIN user_profile up ON tl.processed_by = up.user_id
    JOIN doc_status ds ON tl.doc_status_id = ds.doc_status_id
    ORDER BY tl.log_timestamp DESC LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | PRC DTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Smooth transitions for sidebar width and labels */
        .sidebar-transition { transition: width 0.3s ease-in-out; }
        .label-transition { transition: opacity 0.2s ease-in-out; }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

    <!-- COLLAPSIBLE SIDEBAR -->
    <aside id="sidebar" class="w-72 bg-slate-50 border-r border-slate-200 min-h-screen flex flex-col sidebar-transition relative">
        
        <!-- Toggle Button -->
        <button onclick="toggleSidebar()" class="absolute -right-3 top-8 bg-white border border-slate-200 rounded-full h-7 w-7 flex items-center justify-center text-slate-500 hover:text-blue-900 shadow-sm z-50">
            <i id="toggle-icon" class="fas fa-chevron-left text-xs"></i>
        </button>

        <!-- Sidebar Header (Logo) -->
        <div class="p-6 flex flex-col items-center border-b border-slate-100">
            <img src="prclogo.png" id="sidebar-logo" class="h-16 w-16 transition-all duration-300">
            <div id="sidebar-brand" class="mt-4 text-center overflow-hidden whitespace-nowrap">
                <p class="text-blue-900 font-black text-sm uppercase tracking-tighter">PRC Administration</p>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Control Panel</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 p-4 space-y-2">
            <!-- 1. Dashboard Overview (Active State) -->
            <a href="admin_dashboard.php" class="flex items-center p-3 bg-blue-100 text-blue-900 rounded-lg font-bold transition-colors">
                <i class="fas fa-chart-line w-6 text-center mr-3 text-lg"></i>
                <span class="nav-label opacity-100 whitespace-nowrap">Dashboard Overview</span>
            </a>

            <!-- 2. Upload & Send Document -->
            <a href="transfer.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors group">
                <i class="fas fa-file-upload w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label opacity-100 whitespace-nowrap">Upload Document</span>
            </a>

            <!-- 3. Receive Document -->
            <a href="receive.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors group">
                <i class="fas fa-file-import w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label opacity-100 whitespace-nowrap">Receive Document</span>
            </a>
            
            <div class="my-4 border-t border-slate-100"></div> <!-- Small separator -->

            <!-- 4. User Management -->
            <a href="admin_users.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors group">
                <i class="fas fa-users w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label opacity-100 whitespace-nowrap">User Management</span>
            </a>
            
            <!-- 5. Roles & Permissions -->
            <a href="admin_roles.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors group">
                <i class="fas fa-shield-alt w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label opacity-100 whitespace-nowrap">Roles & Permissions</span>
            </a>

            <!-- Back to App Divider -->
            <div class="pt-10 border-t border-slate-100 mt-6">
                <a href="index.php" class="flex items-center p-3 text-orange-600 hover:bg-orange-50 rounded-lg transition-colors group">
                    <i class="fas fa-arrow-left w-6 text-center mr-3 text-lg"></i>
                    <span class="nav-label opacity-100 whitespace-nowrap font-bold">Back to Main App</span>
                </a>
            </div>
        </nav>
        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-100 text-center overflow-hidden">
            <p id="sidebar-version" class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">DTS v2.0-Admin</p>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 p-10 overflow-y-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">System Overview</h2>
                <p class="text-slate-400 text-sm font-medium">Real-time statistics and transaction logs.</p>
            </div>
            <div class="flex items-center gap-4 bg-white p-2 px-4 rounded-full shadow-sm border border-slate-100">
                <i class="fas fa-user-circle text-blue-900 text-2xl"></i>
                <span class="text-xs font-bold text-slate-600">Administrator Access</span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Total Documents</p>
                        <p class="text-5xl font-black text-blue-900 mt-2"><?php echo $total_docs; ?></p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-xl text-blue-600">
                        <i class="fas fa-file-alt text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Registered Users</p>
                        <p class="text-5xl font-black text-blue-900 mt-2"><?php echo $total_users; ?></p>
                    </div>
                    <div class="bg-green-50 p-3 rounded-xl text-green-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Departments</p>
                        <p class="text-5xl font-black text-blue-900 mt-2"><?php echo $total_depts; ?></p>
                    </div>
                    <div class="bg-purple-50 p-3 rounded-xl text-purple-600">
                        <i class="fas fa-building text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Logs Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-black text-slate-700 text-xs uppercase tracking-widest">Recent System Transactions</h3>
                <span class="text-[10px] bg-white border border-slate-200 px-3 py-1 rounded-full text-slate-400 font-bold uppercase">Last 10 Actions</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-50 font-black uppercase text-[10px] tracking-widest">
                            <th class="p-6">Timestamp</th>
                            <th class="p-6">Document Ref</th>
                            <th class="p-6">Action By</th>
                            <th class="p-6">Status</th>
                            <th class="p-6">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php while($log = $recent_logs->fetch_assoc()): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-6 text-[11px] text-slate-400 font-bold"><?php echo date('M d, h:i A', strtotime($log['log_timestamp'])); ?></td>
                            <td class="p-6 font-bold text-blue-900"><?php echo $log['ref_no']; ?></td>
                            <td class="p-6 text-slate-700 font-semibold"><?php echo $log['user_first_name'] . " " . $log['user_last_name']; ?></td>
                            <td class="p-6">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-black uppercase border border-slate-200">
                                    <?php echo $log['status_name']; ?>
                                </span>
                            </td>
                            <td class="p-6 text-slate-500 italic text-xs max-w-xs truncate"><?php echo $log['remarks']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- SIDEBAR TOGGLE SCRIPT -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const icon = document.getElementById('toggle-icon');
            const labels = document.querySelectorAll('.nav-label');
            const brand = document.getElementById('sidebar-brand');
            const logo = document.getElementById('sidebar-logo');
            const version = document.getElementById('sidebar-version');

            if (sidebar.classList.contains('w-72')) {
                // Collapse
                sidebar.classList.replace('w-72', 'w-20');
                icon.classList.replace('fa-chevron-left', 'fa-chevron-right');
                labels.forEach(l => l.classList.replace('opacity-100', 'opacity-0'));
                setTimeout(() => labels.forEach(l => l.classList.add('hidden')), 200);
                brand.classList.add('hidden');
                version.classList.add('hidden');
                logo.classList.replace('h-16', 'h-10');
                logo.classList.replace('w-16', 'w-10');
            } else {
                // Expand
                sidebar.classList.replace('w-20', 'w-72');
                icon.classList.replace('fa-chevron-right', 'fa-chevron-left');
                labels.forEach(l => l.classList.remove('hidden'));
                setTimeout(() => labels.forEach(l => l.classList.replace('opacity-0', 'opacity-100')), 50);
                brand.classList.remove('hidden');
                version.classList.remove('hidden');
                logo.classList.replace('h-10', 'h-16');
                logo.classList.replace('w-10', 'w-16');
            }
        }
    </script>
</body>
</html>