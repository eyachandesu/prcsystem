<?php
require_once __DIR__ . "/../init.php";
ob_start();

$userData = checkAuth();

// Handle search query if submitted
$searchQuery = $_GET['search'] ?? '';
$searchParam = '%' . $searchQuery . '%';

// Fetch Tracking Documents with JOINs to get actual names instead of IDs
$sql = "SELECT 
            d.doc_id, 
            d.doc_name, 
            d.doc_description, 
            d.doc_updated_at,
            s.doc_status_id,
            s.status_name,
            dept.dept_name,
            CONCAT(up.user_first_name, ' ', IFNULL(up.user_middle_name, ''), ' ', up.user_last_name) AS current_handler
        FROM document d
        LEFT JOIN doc_status s ON d.doc_status_id = s.doc_status_id
        LEFT JOIN department dept ON d.current_dept_id = dept.dept_id
        LEFT JOIN user_profile up ON d.current_user_id = up.user_id
        WHERE d.doc_name LIKE ? OR d.doc_id LIKE ? OR d.doc_description LIKE ?
        ORDER BY d.doc_updated_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="dist/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Document Tracking | PRC DTS</title>
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
            <!-- Active State on Document Tracking -->
            <a href="tracking.php" class="flex items-center p-3 bg-blue-50 text-blue-900 rounded-xl font-bold shadow-sm">
                <i class="fas fa-search w-6 text-center mr-3"></i>
                <span>Document Tracking</span>
            </a>
            <a href="receive.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-file-import w-6 text-center mr-3 group-hover:text-blue-900"></i>
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
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-tighter mt-1"><?= htmlspecialchars($userData->role ?? 'N/A') ?></p>
                </div>
                <div class="h-10 w-10 bg-slate-100 rounded-full flex items-center justify-center border border-slate-200">
                    <i class="fas fa-user text-slate-400"></i>
                </div>
            </div>
        </header>

        <div class="p-10">
            
            <!-- Page Header -->
            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Document Tracking</h1>
                <p class="text-slate-500 mt-1">Search and monitor the real-time location and status of documents.</p>
            </div>

            <!-- Search Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-10">
                <form action="tracking.php" method="GET" class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="flex-1 w-full relative">
                        <i class="fas fa-search absolute left-5 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Enter Tracking Number, Document Name, or Keyword..." 
                               class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-2xl focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-medium">
                    </div>
                    <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-10 rounded-2xl transition-all shadow-md hover:shadow-lg text-sm whitespace-nowrap">
                        Track Document
                    </button>
                    <?php if (!empty($searchQuery)): ?>
                        <a href="tracking.php" class="w-full md:w-auto bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-4 px-6 rounded-2xl transition-all text-sm text-center">
                            Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tracking Results Table -->
            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-10">
                <div class="bg-slate-50 p-4 border-b border-slate-100 px-8 flex justify-between items-center">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Live Tracking Board</span>
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase"><?= count($documents) ?> Records Found</span>
                </div>
                
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full table-auto text-left border-collapse">
                        <thead class="bg-white border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Document / Tracking ID</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Current Location</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Current Handler</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Last Updated</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-50">
                            <?php if (empty($documents)): ?>
                                <tr>
                                    <td colspan="5" class="px-8 py-16 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                                <i class="fas fa-search-minus text-2xl text-slate-300"></i>
                                            </div>
                                            <span class="text-sm font-medium">No documents match your tracking search.</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($documents as $doc): 
                                    // Determine Badge Colors based on doc_status_id
                                    $badgeClass = 'bg-slate-100 text-slate-600'; // Default
                                    $iconClass = 'fa-file';
                                    
                                    switch ($doc['doc_status_id']) {
                                        case 1: // Pending/Uploaded
                                            $badgeClass = 'bg-orange-50 text-orange-600 border border-orange-100';
                                            $iconClass = 'fa-clock';
                                            break;
                                        case 2: // Forwarded/Transferred
                                            $badgeClass = 'bg-blue-50 text-blue-600 border border-blue-100';
                                            $iconClass = 'fa-paper-plane';
                                            break;
                                        case 3: // Received/Action Taken
                                            $badgeClass = 'bg-green-50 text-green-600 border border-green-100';
                                            $iconClass = 'fa-check-double';
                                            break;
                                        case 4: // Archived/Completed
                                            $badgeClass = 'bg-slate-100 text-slate-600 border border-slate-200';
                                            $iconClass = 'fa-archive';
                                            break;
                                    }
                                ?>
                                    <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" onclick="openDetailsModal('<?= htmlspecialchars($doc['doc_id']) ?>')">
                                        <td class="px-8 py-5">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-800 text-sm mb-1"><?= htmlspecialchars($doc['doc_name']) ?></span>
                                                <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest"><?= htmlspecialchars($doc['doc_id']) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-building text-slate-300 text-xs"></i>
                                                <span class="font-medium text-slate-600"><?= htmlspecialchars($doc['dept_name'] ?? 'Unknown Dept') ?></span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] font-bold">
                                                    <?= strtoupper(substr($doc['current_handler'], 0, 1) ?? '?') ?>
                                                </div>
                                                <span class="text-slate-600 font-medium whitespace-nowrap"><?= htmlspecialchars($doc['current_handler'] ?? 'Unassigned') ?></span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 text-center">
                                            <span class="<?= $badgeClass ?> px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-wider flex items-center justify-center gap-1.5 inline-flex">
                                                <i class="fas <?= $iconClass ?>"></i> <?= htmlspecialchars($doc['status_name']) ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <span class="text-xs text-slate-500 font-medium">
                                                <?= date('M d, Y - h:i A', strtotime($doc['doc_updated_at'])) ?>
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
    <!-- DOCUMENT DETAILS/HISTORY MODAL PLACEHOLDER -->
    <!-- ========================================== -->
    <div id="documentDetailsModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeDetailsModal()"></div>
        
        <!-- Modal Content -->
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-2xl relative z-10 overflow-hidden flex flex-col transform transition-all">
            <div class="bg-slate-50 p-5 border-b border-slate-100 flex justify-between items-center px-8">
                <span class="text-xs font-black text-slate-800 uppercase tracking-widest">Document Timeline</span>
                <button onclick="closeDetailsModal()" class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <div class="p-8">
                <div class="text-center mb-8">
                    <i class="fas fa-route text-4xl text-blue-100 mb-4"></i>
                    <h3 class="text-lg font-black text-slate-800">Timeline Feature Coming Soon</h3>
                    <p class="text-sm text-slate-500 mt-2">This is where you would fetch and display the data from the <strong>transaction_logs</strong> table to show the step-by-step history of this document.</p>
                </div>
                
                <div class="flex justify-center border-t border-slate-50 pt-6">
                    <button type="button" onclick="closeDetailsModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-8 rounded-xl transition-all shadow-sm text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const detailsModal = document.getElementById('documentDetailsModal');

        function openDetailsModal(docId) {
            // Note: In a real implementation, you would make an AJAX call here to fetch
            // the transaction_logs for the given docId, and populate the modal with them.
            console.log("Fetching history for document: " + docId);
            detailsModal.classList.remove('hidden');
        }

        function closeDetailsModal() {
            detailsModal.classList.add('hidden');
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !detailsModal.classList.contains('hidden')) {
                closeDetailsModal();
            }
        });
    </script>
</body>
</html>