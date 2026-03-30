<?php
session_start();
require_once "../config/config.php";
require_once "../helper/jwt_helper.php";
// 1. Fetch Recipients (Excluding current user)
$users_query = $conn->prepare("SELECT email, user_first_name, user_last_name FROM user_profile WHERE user_id != ? ORDER BY email ASC");
$users_query->bind_param("s", $_SESSION['user_id']);
$users_query->execute();
$users_list = $users_query->get_result();

// 2. Fetch My Documents
$my_docs = $conn->prepare("
    SELECT d.doc_id, d.applicant_name, d.ref_no, dt.document_type_name 
    FROM document d 
    JOIN document_type dt ON d.doc_type_id = dt.doc_type_id 
    WHERE d.current_user_id = ?
");
$my_docs->bind_param("s", $_SESSION['user_id']);
$my_docs->execute();
$docs_res = $my_docs->get_result();

// Get current date/time
date_default_timezone_set('Asia/Manila');
$current_date = date('m/d/Y');
$current_time = date('h:i A');
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
    
    <!-- MAIN CONTENT -->
    <main class="flex-1 p-10 overflow-y-auto">
        <div class="max-w-5xl mx-auto">
            
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Transfer Document</h2>
                    <p class="text-slate-400 text-sm font-medium">Forwarding documents across departments.</p>
                </div>
                <div class="flex items-center gap-2 bg-green-50 px-4 py-2 rounded-full border border-green-100">
                    <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-[10px] font-black text-green-700 uppercase tracking-widest">Form is Ready</span>
                </div>
            </div>

            <form action="../controllers/DocumentController.php" method="POST" class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                
                <div class="bg-slate-50 p-4 border-b border-slate-100 flex justify-between items-center px-8">
                    <div class="flex gap-4">
                        <button type="button" class="text-[10px] font-black text-slate-400 hover:text-blue-900 uppercase tracking-widest transition-colors">
                            <i class="fas fa-save mr-1"></i> Save Draft
                        </button>
                        <button type="reset" class="text-[10px] font-black text-slate-400 hover:text-red-600 uppercase tracking-widest transition-colors">
                            <i class="fas fa-sync-alt mr-1"></i> Reset Form
                        </button>
                    </div>
                    <button type="submit" name="transfer_document" class="bg-blue-900 text-white px-8 py-2.5 rounded-full font-bold text-xs uppercase shadow-lg hover:bg-blue-800 transition-all flex items-center gap-2">
                        <i class="fas fa-paper-plane text-[10px]"></i> Execute & Send
                    </button>
                </div>

                <div class="p-10">
                    <!-- TOP METADATA ROW -->
                    <div class="grid grid-cols-3 gap-8 mb-12 pb-8 border-b border-slate-50">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Current Date</label>
                            <input type="text" value="<?= $current_date ?>" readonly class="w-full bg-slate-50 border-none p-2 rounded text-sm font-bold text-slate-600 outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Current Time</label>
                            <input type="text" value="<?= $current_time ?>" readonly class="w-full bg-slate-50 border-none p-2 rounded text-sm font-bold text-slate-600 outline-none">
                        </div>
                        <div>
                            <!-- RESTORED DROPDOWN -->
                            <label class="text-[10px] font-black text-blue-900 uppercase tracking-widest block mb-2">Document Code (Ref #)*</label>
                            <select name="doc_id" id="doc_id" required onchange="updateDocDetails()" class="w-full border-b-2 border-blue-900 p-2 outline-none bg-white text-sm font-black text-blue-900">
                                <option value="" disabled selected>-- Select Reference --</option>
                                <?php while($doc = $docs_res->fetch_assoc()): ?>
                                    <option value="<?= $doc['doc_id'] ?>" 
                                            data-name="<?= htmlspecialchars($doc['applicant_name']) ?>" 
                                            data-type="<?= htmlspecialchars($doc['document_type_name']) ?>">
                                        <?= $doc['ref_no'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-16">
                        <div class="space-y-8">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest border-l-4 border-blue-900 pl-3">Document Details</h3>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase block mb-2 tracking-widest">Document Name</label>
                                <input type="text" name="document_name" id="display_doc_name" required class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 bg-white text-sm font-bold text-slate-700 transition-colors" placeholder="Auto-filled or type name...">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase block mb-2 tracking-widest">Classification Type</label>
                                <input type="text" id="display_doc_type" readonly class="w-full border-b border-slate-100 p-2 text-sm font-bold text-slate-500 outline-none" placeholder="Auto-filled...">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase block mb-2 tracking-widest">Transfer By</label>
                                <input type="text" readonly value="System Administrator" class="w-full border-b border-slate-100 p-2 text-sm font-bold text-blue-900 outline-none">
                            </div>
                        </div>

                        <div class="space-y-8">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest border-l-4 border-orange-500 pl-3">Transfer Destination</h3>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase block mb-2 tracking-widest">Addressee / Recipient*</label>
                                <select name="target_email" required class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 bg-white text-sm font-bold">
                                    <option value="" disabled selected>Select Recipient Email</option>
                                    <?php while($u = $users_list->fetch_assoc()): ?>
                                        <option value="<?= $u['email'] ?>">
                                            <?= $u['user_first_name'] ?> <?= $u['user_last_name'] ?> (<?= $u['email'] ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase block mb-2 tracking-widest">Action Taken</label>
                                <textarea name="action_taken" class="w-full border border-slate-100 p-4 rounded-xl text-xs h-20 outline-none focus:border-blue-900 transition-all bg-slate-50/30" placeholder="e.g. Reviewed and verified signatures..."></textarea>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase block mb-2 tracking-widest">Remarks</label>
                                <textarea name="remarks" required class="w-full border border-slate-100 p-4 rounded-xl text-xs h-20 outline-none focus:border-blue-900 transition-all bg-slate-50/30" placeholder="Specify routing notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        function updateDocDetails() {
            const select = document.getElementById('doc_id');
            const selectedOption = select.options[select.selectedIndex];
            if(selectedOption.value !== "") {
                document.getElementById('display_doc_name').value = selectedOption.getAttribute('data-name');
                document.getElementById('display_doc_type').value = selectedOption.getAttribute('data-type');
            }
        }

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