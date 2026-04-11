<?php
require_once __DIR__ . "/../init.php";
ob_start();

$userData = checkAuth();
$current_user_id = $userData->user_id;
$user_role = $userData->role;
$user_dept_id = $userData->dept_id;
$visibility = new DocVisibility($conn);
$documents = $visibility->getVisibleDocuments($user_role, $user_dept_id);
$deptOptions = fetchDept($conn);
$userbyDept = fetchUsersByDepartment($conn, $user_dept_id);
var_dump($user_dept_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transfer Document | PRC DTS</title>
    <!-- Keep CDN for immediate styling, but also link your local file -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="dist/output.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar-transition { transition: width 0.3s ease-in-out; }
        input:read-only { cursor: not-allowed; background-color: #f8fafc; }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen font-sans">

    <!-- SIDEBAR -->
    <aside id="sidebar" class="w-72 bg-slate-50 border-r border-slate-200 min-h-screen flex flex-col sidebar-transition relative">
        <button onclick="toggleSidebar()" class="absolute -right-3 top-10 bg-white border border-slate-200 rounded-full h-7 w-7 flex items-center justify-center text-slate-500 hover:text-blue-900 shadow-sm z-50">
            <i id="toggle-icon" class="fas fa-chevron-left text-xs"></i>
        </button>

        <div class="p-6 flex flex-col items-center border-b border-slate-100">
            <img src="img/prclogo.png" id="sidebar-logo" class="h-16 w-16" style="mix-blend-mode: multiply;">
            <div id="sidebar-brand" class="mt-4 text-center">
                <p class="text-blue-900 font-black text-sm uppercase tracking-tighter">PRC Administration</p>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-2">
            <a href="admin_dashboard.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg group">
                <i class="fas fa-chart-line w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label whitespace-nowrap">Overview</span>
            </a>
            <a href="tracking.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg group">
                <i class="fas fa-search w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label whitespace-nowrap">Document Tracking</span>
            </a>
            <a href="receive.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg group">
                <i class="fas fa-file-import w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label">Receive Document</span>
            </a>
            <a href="transfer.php" class="flex items-center p-3 bg-blue-100 text-blue-900 rounded-lg font-bold shadow-sm">
                <i class="fas fa-exchange-alt w-6 text-center mr-3 text-lg"></i>
                <span class="nav-label whitespace-nowrap">Transfer Document</span>
            </a>
        </nav>
    </aside>
    
   <h1>Transfer Document</h1>
    <div>
        <form action="../controllers/transfer_document.php" method="POST">
            <div>
                <h2>Transfer Document</h2>
                <div>
                    <!-- Document Dropdown -->
                    <label for="documents">Select Document</label>
                    <select name="documents" id="documents">
                        <option value="" disabled selected>Select Document</option>
                        <?php foreach ($documents as $doc): ?>
                            <option value="<?= htmlspecialchars($doc['doc_id']) ?>">
                                <?= htmlspecialchars($doc['doc_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                        <!--Department Dropdown -->
                    <label for="departments">Select Department</label>
                    <select name="departments" id="departments">
                        <option value="" disabled selected>Select Department</option>
                        <?php foreach ($deptOptions as $dept): ?>
                            <option value="<?= htmlspecialchars($dept['dept_id']) ?>">
                                <?= htmlspecialchars($dept['dept_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- User Dropdown -->
                    <label for="user">Select User</label>
                    <select name="user" id="user" disabled class="bg-gray-100 cursor-not-allowed">
                        <option value="" disabled selected>Select User</option>
                        <?php foreach ($userbyDept as $user): ?>
                            <option value="<?= htmlspecialchars($user['user_id']) ?>">
                                <?= htmlspecialchars($user['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">
                        Transfer Document
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
<script src="js/transfer.js"></script>
</html>