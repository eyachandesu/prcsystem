<?php
require_once __DIR__ . "/../init.php";
ob_start();

$userData = checkAuth();
$user_dept_id = $userData->dept_id;

// Fetch department name for the logged-in user
$user_dept_name = "Unknown Department";
if (isset($userData->dept_name)) {
    $user_dept_name = $userData->dept_name;
} else {
    $dept_sql = "SELECT dept_name FROM department WHERE dept_id = ?";
    if ($dept_stmt = $conn->prepare($dept_sql)) {
        $dept_stmt->bind_param("i", $user_dept_id);
        $dept_stmt->execute();
        $dept_result = $dept_stmt->get_result();
        if ($dept_row = $dept_result->fetch_assoc()) {
            $user_dept_name = $dept_row['dept_name'];
        }
        $dept_stmt->close();
    }
}

// Fetch documents that are currently in this department AND have the status "Forwarded/Transferred" (ID = 2)
$sql = "SELECT 
            d.doc_id, 
            d.doc_name, 
            d.doc_description, 
            d.doc_updated_at,
            CONCAT(up.user_first_name, ' ', IFNULL(up.user_middle_name, ''), ' ', up.user_last_name) AS uploader_name
        FROM document d
        LEFT JOIN user_profile up ON d.uploaded_by = up.user_id
        WHERE d.current_dept_id = ? AND d.doc_status_id = 2
        ORDER BY d.doc_updated_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_dept_id);
$stmt->execute();
$result = $stmt->get_result();
$incomingDocs = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="dist/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Receive Document | PRC DTS</title>
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
            <a href="documents.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-file w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Documents</span>
            </a>
            <a href="tracking.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-search w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Document Tracking</span>
            </a>
            <!-- Active State on Receive Document -->
            <a href="receive.php" class="flex items-center p-3 bg-blue-50 text-blue-900 rounded-xl font-bold shadow-sm">
                <i class="fas fa-file-import w-6 text-center mr-3"></i>
                <span>Receive Document</span>
            </a>
            <a href="users.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-users-cog w-6 text-center mr-3 group-hover:text-blue-900"></i>
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
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-tighter mt-1">
                        <?= htmlspecialchars($userData->role ?? 'N/A') ?> 
                        <span class="text-slate-300 mx-1">&bull;</span> 
                        <span class="text-slate-500"><?= htmlspecialchars($user_dept_name) ?></span>
                    </p>
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
            
            <!-- Page Header -->
            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Department Inbox</h1>
                <p class="text-slate-500 mt-1">Acknowledge and receive documents that have been transferred to your department.</p>
            </div>

            <!-- Incoming Documents Table Card -->
            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-10">
                <div class="bg-slate-50 p-4 border-b border-slate-100 px-8 flex justify-between items-center">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Incoming Documents</span>
                    <?php if (!empty($incomingDocs)): ?>
                        <span class="flex items-center gap-2 text-[10px] font-bold text-orange-600 bg-orange-50 px-3 py-1 rounded-full uppercase tracking-wider animate-pulse">
                            <span class="h-2 w-2 rounded-full bg-orange-500"></span> Action Required
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full table-auto text-left border-collapse">
                        <thead class="bg-white border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Document Details</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Description</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Origin / Uploader</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date Transferred</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-50">
                            <?php if (empty($incomingDocs)): ?>
                                <tr>
                                    <td colspan="5" class="px-8 py-16 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4 border border-slate-100 shadow-inner">
                                                <i class="fas fa-inbox text-2xl text-slate-300"></i>
                                            </div>
                                            <span class="text-sm font-medium text-slate-500">No incoming documents at the moment.</span>
                                            <p class="text-xs text-slate-400 mt-1">You're all caught up!</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($incomingDocs as $doc): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-8 py-5">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-800 text-sm mb-1"><?= htmlspecialchars($doc['doc_name']) ?></span>
                                                <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest"><?= htmlspecialchars($doc['doc_id']) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <span class="text-slate-600 max-w-xs truncate block" title="<?= htmlspecialchars($doc['doc_description']) ?>">
                                                <?= htmlspecialchars($doc['doc_description']) ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-user-circle text-slate-300"></i>
                                                <span class="text-slate-600 font-medium whitespace-nowrap"><?= htmlspecialchars($doc['uploader_name']) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <span class="text-xs text-slate-500 font-medium whitespace-nowrap">
                                                <?= date('M d, Y - h:i A', strtotime($doc['doc_updated_at'])) ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <button onclick="openReceiveModal('<?= htmlspecialchars($doc['doc_id']) ?>', '<?= htmlspecialchars(addslashes($doc['doc_name'])) ?>')" 
                                                    class="bg-green-50 hover:bg-green-600 text-green-600 hover:text-white border border-green-200 hover:border-green-600 font-bold py-2 px-5 rounded-xl transition-all shadow-sm text-xs inline-flex items-center gap-2">
                                                <i class="fas fa-check-double"></i> Receive
                                            </button>
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
    <!-- RECEIVE DOCUMENT MODAL                     -->
    <!-- ========================================== -->
    <div id="receiveDocumentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeReceiveModal()"></div>
        
        <!-- Modal Content -->
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md relative z-10 overflow-hidden flex flex-col transform transition-all">
            <div class="bg-slate-50 p-5 border-b border-slate-100 flex justify-between items-center px-8">
                <span class="text-xs font-black text-slate-800 uppercase tracking-widest">Acknowledge Receipt</span>
                <button onclick="closeReceiveModal()" class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <div class="p-8">
                <form action="../controllers/receive_document.php" method="POST">
                    
                    <input type="hidden" name="doc_id" id="receive_doc_id">

                    <div class="mb-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                            <i class="fas fa-file-import text-green-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 mb-1" id="receive_doc_name">Document Name</h3>
                        <p class="text-xs text-slate-500">You are about to officially receive this document into your department.</p>
                    </div>

                    <div class="mb-8">
                        <label for="remarks" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Remarks / Notes (Optional)</label>
                        <textarea name="remarks" id="remarks" rows="3" placeholder="E.g., Received in good condition, Action required..." 
                                  class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block p-3 outline-none transition-all resize-none"></textarea>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-50">
                        <button type="button" onclick="closeReceiveModal()" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold py-2.5 px-6 rounded-xl transition-all text-sm">
                            Cancel
                        </button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm text-sm flex items-center gap-2">
                            <i class="fas fa-check"></i> Confirm Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const receiveModal = document.getElementById('receiveDocumentModal');
        const docIdInput = document.getElementById('receive_doc_id');
        const docNameText = document.getElementById('receive_doc_name');

        function openReceiveModal(docId, docName) {
            docIdInput.value = docId;
            docNameText.textContent = docName;
            receiveModal.classList.remove('hidden');
        }

        function closeReceiveModal() {
            receiveModal.classList.add('hidden');
            document.getElementById('remarks').value = ''; // Reset remarks
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !receiveModal.classList.contains('hidden')) {
                closeReceiveModal();
            }
        });
    </script>
</body>
</html>