<?php
session_start();
require_once "../config/config.php";
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// Helper function for "Age of Document" (Legacy Feature)
function getAge($date) {
    $start = new DateTime($date);
    $end = new DateTime();
    $diff = $start->diff($end);
    return $diff->y . "y " . $diff->m . "m " . $diff->d . "d";
}

// Fetch filter options
$doc_types = $conn->query("SELECT * FROM document_type ORDER BY document_type_name ASC");

// Fetch documents (with basic search/filter logic placeholder)
$query = "SELECT d.*, dt.document_type_name, ds.status_name 
          FROM document d 
          JOIN document_type dt ON d.doc_type_id = dt.doc_type_id
          JOIN doc_status ds ON d.doc_status_id = ds.doc_status_id
          ORDER BY d.doc_created_at DESC";
$documents = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Tracking | PRC DTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar-transition { transition: width 0.3s ease-in-out; }
        .nav-label { transition: opacity 0.2s; }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

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
                <span class="nav-label opacity-100 whitespace-nowrap">Dashboard</span>
            </a>
            
            <a href="tracking.php" class="flex items-center p-3 bg-blue-100 text-blue-900 rounded-lg font-bold shadow-sm">
                <i class="fas fa-search w-6 text-center mr-3 text-lg"></i>
                <span class="nav-label opacity-100 whitespace-nowrap">Document Tracking</span>
            </a>
            <a href="receive.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg group">
                <i class="fas fa-file-import w-6 text-center mr-3 text-lg"></i>
                <span class="nav-label">Receive Document</span>
            </a>
            <a href="transfer.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg group">
                <i class="fas fa-file-upload w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label opacity-100 whitespace-nowrap">Upload Document</span>
            </a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-10 overflow-y-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Document Tracking</h2>
                <p class="text-slate-400 text-sm font-medium">Search and monitor document lifecycles.</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Current Date</p>
                <p class="text-sm font-bold text-slate-700"><?php echo date('F d, Y'); ?></p>
            </div>
        </div>

        <!-- FILTERS CARD -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Classification</label>
                    <select class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold bg-transparent">
                        <option>Active Files</option>
                        <option>Archived</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Document Type</label>
                    <select class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold bg-transparent">
                        <option value="All">All Types</option>
                        <?php while($dt = $doc_types->fetch_assoc()): ?>
                            <option value="<?= $dt['doc_type_id'] ?>"><?= $dt['document_type_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Date From</label>
                    <input type="date" class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold bg-transparent">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Date To</label>
                    <input type="date" class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold bg-transparent">
                </div>
            </div>
            <div class="mt-8 flex gap-3">
                <button class="bg-blue-900 text-white px-8 py-2.5 rounded-xl font-bold text-xs uppercase shadow-lg hover:bg-blue-800 transition-all">
                    <i class="fas fa-search mr-2"></i> View Records
                </button>
                <button class="bg-slate-100 text-slate-500 px-8 py-2.5 rounded-xl font-bold text-xs uppercase hover:bg-slate-200 transition-all">
                    <i class="fas fa-sync-alt mr-2"></i> Reset
                </button>
            </div>
        </div>

        <!-- RESULTS TABLE -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="p-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Document Registry</span>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Rows:</span>
                    <select class="text-xs font-bold border rounded px-2 py-1 bg-white">
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="p-6">Date Uploaded</th>
                            <th class="p-6">Document Type</th>
                            <th class="p-6">Document Name</th>
                            <th class="p-6">Age of Document</th>
                            <th class="p-6">Status</th>
                            <th class="p-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php while($doc = $documents->fetch_assoc()): ?>
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="p-6 text-xs font-bold text-slate-500">
                                <?= date('m/d/Y', strtotime($doc['doc_created_at'])) ?>
                            </td>
                            <td class="p-6 text-xs font-bold text-slate-600">
                                <?= $doc['document_type_name'] ?>
                            </td>
                            <td class="p-6 text-xs font-bold text-slate-800">
                                <?= $doc['document_name'] ?>
                            </td>
                            <td class="p-6">
                                <span class="text-[11px] font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded">
                                    <i class="far fa-clock mr-1"></i> <?= getAge($doc['doc_created_at']) ?>
                                </span>
                            </td>
                            <td class="p-6">
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase border border-slate-200 bg-white text-slate-400">
                                    <?= $doc['status_name'] ?>
                                </span>
                            </td>
                            <td class="p-6 text-center">
                                <button class="text-slate-300 hover:text-blue-900 transition-colors">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <!-- PAGINATION FOOTER -->
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Showing 1 - 10 of 180 records</p>
                <div class="flex gap-1">
                    <button class="h-8 w-8 flex items-center justify-center rounded bg-white border border-slate-200 text-slate-400 hover:text-blue-900"><i class="fas fa-chevron-left text-xs"></i></button>
                    <button class="h-8 w-8 flex items-center justify-center rounded bg-blue-900 text-white text-xs font-bold">1</button>
                    <button class="h-8 w-8 flex items-center justify-center rounded bg-white border border-slate-200 text-slate-400 hover:text-blue-900 font-bold text-xs">2</button>
                    <button class="h-8 w-8 flex items-center justify-center rounded bg-white border border-slate-200 text-slate-400 hover:text-blue-900"><i class="fas fa-chevron-right text-xs"></i></button>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const icon = document.getElementById('toggle-icon');
            const labels = document.querySelectorAll('.nav-label');
            const brand = document.getElementById('sidebar-brand');
            const logo = document.getElementById('sidebar-logo');

            if (sidebar.classList.contains('w-72')) {
                sidebar.classList.replace('w-72', 'w-20');
                icon.classList.replace('fa-chevron-left', 'fa-chevron-right');
                labels.forEach(l => l.classList.add('hidden'));
                brand.classList.add('hidden');
                logo.classList.replace('h-16', 'h-10');
            } else {
                sidebar.classList.replace('w-20', 'w-72');
                icon.classList.replace('fa-chevron-right', 'fa-chevron-left');
                labels.forEach(l => l.classList.remove('hidden'));
                brand.classList.remove('hidden');
                logo.classList.replace('h-10', 'h-16');
            }
        }
    </script>
</body>
</html>