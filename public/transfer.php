<?php
session_start();
require_once "../config/config.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$doc_types = $conn->query("SELECT * FROM document_type ORDER BY document_type_name ASC");
$departments = $conn->query("SELECT * FROM department ORDER BY dept_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Document | PRC DTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar-transition { transition: width 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

    <!-- SIDEBAR (Light, Collapsible) -->
    <aside id="sidebar" class="w-72 bg-slate-50 border-r border-slate-200 min-h-screen flex flex-col sidebar-transition relative">
        <button onclick="toggleSidebar()" class="absolute -right-3 top-10 bg-white border border-slate-200 rounded-full h-7 w-7 flex items-center justify-center text-slate-500 hover:text-blue-900 shadow-sm z-50">
            <i id="toggle-icon" class="fas fa-chevron-left text-xs"></i>
        </button>

        <div class="p-6 flex flex-col items-center border-b border-slate-100">
            <img src="prclogo.png" id="sidebar-logo" class="h-16 w-16" style="mix-blend-mode: multiply;">
            <div id="sidebar-brand" class="mt-4 text-center">
                <p class="text-blue-900 font-black text-sm uppercase tracking-tighter">PRC Administration</p>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-2">
            <a href="admin_dashboard.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg group">
                <i class="fas fa-search w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label opacity-100 whitespace-nowrap">Document Tracking</span>
            </a>
            
            <a href="transfer.php" class="flex items-center p-3 bg-blue-100 text-blue-900 rounded-lg font-bold shadow-sm">
                <i class="fas fa-file-upload w-6 text-center mr-3 text-lg"></i>
                <span class="nav-label opacity-100 whitespace-nowrap">Upload Document</span>
            </a>

            <a href="receive.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg group">
                <i class="fas fa-file-import w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label opacity-100 whitespace-nowrap">Receive Document</span>
            </a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-10 overflow-y-auto">
        <div class="max-w-4xl mx-auto">
            <div class="mb-10">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">System Upload</h2>
                <p class="text-slate-400 text-sm font-medium">Initialize document tracking for new arrivals.</p>
            </div>

            <form action="../controllers/DocumentController.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="bg-slate-50 p-4 border-b border-slate-100 flex justify-between items-center px-8">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Entry Form</span>
                    <button type="submit" name="upload_document" class="bg-blue-900 text-white px-8 py-2 rounded-full font-bold text-xs uppercase shadow-lg hover:bg-blue-800 transition-all">
                        Execute Upload
                    </button>
                </div>

                <div class="p-10 grid grid-cols-2 gap-10">
                    <!-- Column 1 -->
                    <div class="space-y-8">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase block mb-2 tracking-widest">Document Name / Title*</label>
                            <input type="text" name="document_name" required class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 transition-colors text-base font-bold text-blue-900" placeholder="e.g. Board Exam Results Oct 2024">
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase block mb-2 tracking-widest">Classification Type</label>
                            <select name="doc_type_id" required class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 bg-white text-sm font-semibold">
                                <?php while($dt = $doc_types->fetch_assoc()): ?>
                                    <option value="<?php echo $dt['doc_type_id']; ?>"><?php echo $dt['document_type_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase block mb-2 tracking-widest">Target Department</label>
                            <select name="target_dept_id" required class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 bg-white text-sm font-semibold">
                                <?php while($dept = $departments->fetch_assoc()): ?>
                                    <option value="<?php echo $dept['dept_id']; ?>"><?php echo $dept['dept_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Column 2 -->
                    <div class="space-y-8">
                        <div class="bg-slate-50 p-6 rounded-2xl border-2 border-dashed border-slate-200 text-center">
                            <label class="text-[10px] font-black text-blue-900 uppercase block mb-4 tracking-widest">Select Scanned File</label>
                            <input type="file" name="doc_file" required class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-6 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-900 file:text-white hover:file:bg-blue-800 transition-all">
                            <p class="text-[9px] text-slate-400 mt-4 font-bold uppercase italic">Allowed: PDF, JPG, PNG (Max 10MB)</p>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase block mb-2 tracking-widest">Routing Instructions / Remarks</label>
                            <textarea name="remarks" class="w-full border-2 border-slate-100 p-4 rounded-2xl text-xs h-32 outline-none focus:border-blue-900 transition-all bg-slate-50/50" placeholder="Specify if urgent or requires specific signatures..."></textarea>
                        </div>
                    </div>
                </div>
            </form>
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