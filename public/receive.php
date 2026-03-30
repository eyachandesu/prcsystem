<?php
session_start();
require_once "../config/config.php";
require_once "../helper/jwt_helper.php";
require_once "../helper/generalValidationMessage.php";
$user_id = $_SESSION['user_id'];

// Fetch documents that are assigned to me but are still in "Forwarded/Transferred" status (Status ID 2)
$pending_docs = $conn->prepare("
    SELECT d.doc_id, d.ref_no, d.applicant_name, d.doc_created_at, dt.document_type_name, 
           up.user_first_name as sender_fn, up.user_last_name as sender_ln
    FROM document d
    JOIN document_type dt ON d.doc_type_id = dt.doc_type_id
    JOIN transaction_logs tl ON d.doc_id = tl.doc_id
    JOIN user_profile up ON tl.processed_by = up.user_id
    WHERE d.current_user_id = ? AND d.doc_status_id = 2
    AND tl.log_id = (SELECT MAX(log_id) FROM transaction_logs WHERE doc_id = d.doc_id)
");
$pending_docs->bind_param("s", $user_id);
$pending_docs->execute();
$results = $pending_docs->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receive Documents | PRC DTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex min-h-screen font-sans">

    <!-- SIDEBAR (Re-use your sidebar component) -->
    <aside id="sidebar" class="w-72 bg-slate-50 border-r border-slate-200 min-h-screen flex flex-col relative transition-all duration-300">
        <div class="p-6 flex flex-col items-center border-b border-slate-100">
            <img src="img/prclogo.png" class="h-16 w-16" style="mix-blend-mode: multiply;">
            <p class="text-blue-900 font-black text-sm uppercase mt-4">PRC Administration</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="admin_dashboard.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg group">
                <i class="fas fa-chart-line w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label font-bold">Overview</span>
            </a>
             <a href="tracking.php" class="flex items-center p-3 text-slate-600 hover:bg-slate-100 rounded-lg group">
                <i class="fas fa-search w-6 text-center mr-3 text-lg group-hover:text-blue-900"></i>
                <span class="nav-label opacity-100 whitespace-nowrap">Document Tracking</span>
            </a>
            <a href="receive.php" class="flex items-center p-3 bg-blue-100 text-blue-900 rounded-lg font-bold shadow-sm">
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
        <div class="max-w-6xl mx-auto">
            <div class="mb-10">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Incoming Documents</h2>
                <p class="text-slate-400 text-sm font-medium">Accept and acknowledge documents sent to your department.</p>
            </div>

            <?php if ($results->num_rows > 0): ?>
                <div class="grid grid-cols-1 gap-6">
                    <?php while($row = $results->fetch_assoc()): ?>
                        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                            <form action="../controllers/DocumentController.php" method="POST">
                                <input type="hidden" name="doc_id" value="<?= $row['doc_id'] ?>">
                                
                                <div class="p-8 flex flex-col md:flex-row gap-8">
                                    <!-- Doc Details -->
                                    <div class="flex-1 space-y-4">
                                        <div class="flex items-center gap-3">
                                            <span class="bg-blue-900 text-white px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest"><?= $row['ref_no'] ?></span>
                                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest"><?= $row['document_type_name'] ?></span>
                                        </div>
                                        <h3 class="text-xl font-bold text-slate-800"><?= $row['applicant_name'] ?></h3>
                                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">
                                            <i class="fas fa-user-circle mr-1"></i> Sent By: <span class="text-blue-900"><?= $row['sender_fn'] ?> <?= $row['sender_ln'] ?></span>
                                        </p>
                                    </div>

                                    <!-- Remark Inputs -->
                                    <div class="flex-[2] grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Action Taken</label>
                                            <textarea name="action_taken" required class="w-full h-20 p-3 bg-slate-50 border border-slate-100 rounded-xl text-xs outline-none focus:border-blue-900 transition-all" placeholder="What was done with this?"></textarea>
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Final Remarks</label>
                                            <textarea name="remarks" required class="w-full h-20 p-3 bg-slate-50 border border-slate-100 rounded-xl text-xs outline-none focus:border-blue-900 transition-all" placeholder="Reception notes..."></textarea>
                                        </div>
                                    </div>

                                    <!-- Submit -->
                                    <div class="flex items-center">
                                        <button type="submit" name="receive_document" class="bg-blue-900 hover:bg-blue-800 text-white p-4 rounded-2xl shadow-lg transition-all group">
                                            <i class="fas fa-check-double text-xl"></i>
                                            <p class="text-[9px] font-black uppercase mt-1">Receive</p>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-200">
                    <i class="fas fa-inbox text-slate-200 text-6xl mb-4"></i>
                    <p class="text-slate-400 font-bold italic">No documents waiting for reception.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>