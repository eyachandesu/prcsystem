<?php
require_once __DIR__ . "/../init.php";
ob_start();

$userData = checkAuth();
$current_user_id = $userData->user_id;
$user_role = $userData->role;
$user_dept_id = $userData->dept_id;

$visibility = new DocVisibility($conn);
$documents = $visibility->getVisibleDocuments($user_role, $user_dept_id);

// Ensure these variables are populated for your Transfer Document modal dropdowns
$deptOptions = fetchDept($conn) ?? []; 
$userbyDept =[]; // Fetch this depending on how your backend handles it
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="dist/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Documents | PRC DTS</title>
</head>

<body class="bg-slate-50 flex min-h-screen font-sans relative">

    <!-- SIDEBAR -->
    <aside class="w-72 bg-white border-r border-slate-200 flex flex-col hidden md:flex">
        <div class="p-8 border-b border-slate-50 text-center">
            <img src="img/prclogo.png" class="h-16 w-16 mx-auto mb-4" style="mix-blend-mode: multiply;">
            <p class="text-blue-900 font-black text-sm uppercase tracking-tighter">PRC Administration</p>
        </div>

        <nav class="flex-1 p-6 space-y-3">
            <a href="admin_dashboard.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-chart-line w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Overview</span>
            </a>
            <a href="documents.php" class="flex items-center p-3 bg-blue-50 text-blue-900 rounded-xl font-bold shadow-sm">
                <i class="fas fa-file w-6 text-center mr-3"></i>
                <span>Documents</span>
            </a>
            <a href="tracking.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-search w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Document Tracking</span>
            </a>
    
            <a href="receive.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-file-import w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Receive Document</span>
            </a>
            <a href="users.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-users-cog w-6 text-center mr-3"></i>
                <span>User Management</span>
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
    <main class="flex-1 flex flex-col h-screen overflow-y-auto relative">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200 h-20 flex-shrink-0 flex items-center justify-between px-10">
            <div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest">Document Management</h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-800 leading-none"><?= htmlspecialchars($userData->full_name ?? 'Unknown User') ?></p>
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-tighter mt-1"><?= htmlspecialchars($userData->role ?? 'N/A') ?></p>
                </div>
                <div class="h-10 w-10 bg-slate-100 rounded-full flex items-center justify-center border border-slate-200">
                    <i class="fas fa-user text-slate-400"></i>
                </div>
            </div>
        </header>

        <div class="p-10">
            
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        <span class="text-sm font-bold"><?= htmlspecialchars($_SESSION['success']); ?></span>
                    </div>
                    <button onclick="this.parentElement.style.display='none'" class="text-green-500 hover:text-green-700 outline-none"><i class="fas fa-times"></i></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                        <span class="text-sm font-bold"><?= htmlspecialchars($_SESSION['error']); ?></span>
                    </div>
                    <button onclick="this.parentElement.style.display='none'" class="text-red-500 hover:text-red-700 outline-none"><i class="fas fa-times"></i></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Page Header with Buttons -->
            <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Documents</h1>
                    <p class="text-slate-500 mt-1">View, manage, and add new documents to the tracking system.</p>
                </div>
                
                <!-- Action Buttons Grouped -->
                <div class="flex items-center gap-3">
                    <button onclick="openTransferModal()" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold py-3 px-6 rounded-xl transition-all shadow-sm text-sm flex items-center gap-2">
                        <i class="fas fa-exchange-alt text-blue-600"></i> Transfer Document
                    </button>
                    <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-md hover:shadow-lg text-sm flex items-center gap-2">
                        <i class="fas fa-plus"></i> Add New Document
                    </button>
                </div>
            </div>

            <!-- Documents Table Card -->
            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-10">
                <div class="bg-slate-50 p-4 border-b border-slate-100 px-8">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Document Repository</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto text-left">
                        <thead class="bg-white border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Document Name</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Description</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Uploaded By</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Current Department</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Handled By</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php if (empty($documents)): ?>
                                <tr>
                                    <td colspan="6" class="px-8 py-10 text-center text-slate-400 italic">No documents found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($documents as $document): ?>
                                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                        <td class="px-8 py-4 font-mono text-blue-600 font-bold uppercase text-xs">
                                            <?= htmlspecialchars($document['doc_name']) ?>
                                        </td>
                                        <td class="px-8 py-4 text-slate-600 max-w-xs truncate">
                                            <?= htmlspecialchars($document['doc_description']) ?>
                                        </td>
                                        <td class="px-8 py-4 text-slate-800 font-medium">
                                            <?= htmlspecialchars($document['uploader']) ?>
                                        </td>
                                        <td class="px-8 py-4 text-slate-600">
                                            <?= htmlspecialchars($document['department']) ?>
                                        </td>
                                        <td class="px-8 py-4 text-slate-600">
                                            <?= htmlspecialchars($document['current_handler']) ?>
                                        </td>
                                        <td class="px-8 py-4">
                                            <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                                <?= htmlspecialchars($document['document_status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- ========================================== -->
    <!-- ADD DOCUMENT MODAL                         -->
    <!-- ========================================== -->
    <div id="addDocumentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Overlay Backdrop -->
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        
        <!-- Modal Content -->
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg relative z-10 overflow-hidden flex flex-col transform transition-all">
            <!-- Modal Header -->
            <div class="bg-slate-50 p-5 border-b border-slate-100 flex justify-between items-center px-8">
                <span class="text-xs font-black text-slate-800 uppercase tracking-widest">Add New Document</span>
                <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-8">
                <form action="/controllers/add_document.php" method="POST">
                    <div class="mb-5">
                        <label for="document_name" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Document Name</label>
                        <input type="text" name="document_name" id="document_name" placeholder="Enter Document Name" required 
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all">
                    </div>
                    <div class="mb-8">
                        <label for="document_desc" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Description</label>
                        <textarea name="document_desc" id="document_desc" rows="3" placeholder="Enter Document Description" 
                                  class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all resize-none"></textarea>
                    </div>
                    
                    <!-- Modal Footer / Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-50">
                        <button type="button" onclick="closeModal()" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold py-2.5 px-6 rounded-xl transition-all text-sm">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm text-sm flex items-center gap-2">
                            <i class="fas fa-save"></i> Save Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TRANSFER DOCUMENT MODAL                    -->
    <!-- ========================================== -->
    <div id="transferDocumentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Overlay Backdrop -->
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeTransferModal()"></div>
        
        <!-- Modal Content -->
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md relative z-10 overflow-hidden flex flex-col transform transition-all">
            <div class="bg-slate-50 p-5 border-b border-slate-100 flex justify-between items-center px-8">
                <span class="text-xs font-black text-slate-800 uppercase tracking-widest">Transfer Document</span>
                <button onclick="closeTransferModal()" class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <div class="p-8">
                <form action="../controllers/transfer_document.php" method="POST">
                    
                    <!-- Document Dropdown -->
                    <div class="mb-5">
                        <label for="documents" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Select Document</label>
                        <select name="documents" id="documents" required 
                                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all cursor-pointer">
                            <option value="" disabled selected>Select Document</option>
                            <?php foreach ($documents as $doc): ?>
                                <option value="<?= htmlspecialchars($doc['doc_id']) ?>">
                                    <?= htmlspecialchars($doc['doc_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Department Dropdown -->
                    <div class="mb-5">
                        <label for="departments" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Select Department</label>
                        <select name="departments" id="departments" required 
                                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all cursor-pointer">
                            <option value="" disabled selected>Select Department</option>
                            <?php foreach ($deptOptions as $dept): ?>
                                <option value="<?= htmlspecialchars($dept['dept_id']) ?>">
                                    <?= htmlspecialchars($dept['dept_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- User Dropdown -->
                    <div class="mb-8">
                        <label for="user" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Select User</label>
                        <select name="user" id="user" disabled 
                                class="w-full bg-slate-100 border border-slate-200 text-slate-400 text-sm rounded-xl cursor-not-allowed block p-3 outline-none transition-all disabled:opacity-75">
                            <option value="" disabled selected>Select User</option>
                            <?php if (!empty($userbyDept)): ?>
                                <?php foreach ($userbyDept as $user): ?>
                                    <option value="<?= htmlspecialchars($user['user_id']) ?>">
                                        <?= htmlspecialchars($user['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-50">
                        <button type="button" onclick="closeTransferModal()" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold py-2.5 px-6 rounded-xl transition-all text-sm">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm text-sm flex items-center gap-2">
                            <i class="fas fa-paper-plane"></i> Transfer Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS for Modal Interactivity -->
    <script>
        const addModal = document.getElementById('addDocumentModal');
        const transferModal = document.getElementById('transferDocumentModal');

        function openModal() {
            addModal.classList.remove('hidden');
        }

        function closeModal() {
            addModal.classList.add('hidden');
        }

        function openTransferModal() {
            transferModal.classList.remove('hidden');
        }

        function closeTransferModal() {
            transferModal.classList.add('hidden');
        }

        // Allow closing the modals using the Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                if (!addModal.classList.contains('hidden')) closeModal();
                if (!transferModal.classList.contains('hidden')) closeTransferModal();
            }
        });
    </script>
</body>
</html>