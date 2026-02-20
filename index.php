<?php
session_start();

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php'; // Or your database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Tracking | PRC DTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <!-- Top Navigation Header -->
    <header class="bg-white border-b shadow-sm p-3 flex justify-between items-center px-6">
        <div class="flex items-center">
            <img src="prclogo.png" alt="PRC Logo" class="h-12 w-12 mr-4">
            <div>
                <h1 class="text-blue-900 font-bold text-lg leading-tight">Document Tracking System</h1>
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Professional Regulation Commission Region 1</p>
            </div>
        </div>
        <div class="text-sm flex items-center gap-4">
            <div>
                Welcome, <span class="font-bold text-blue-700"><?php echo htmlspecialchars($_SESSION['name']); ?></span> 
                <span class="text-gray-400 mx-1">|</span>
                <span class="text-xs uppercase font-medium bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
            </div>
            <a href="logout.php" class="ml-4 text-red-600 font-bold hover:text-red-800 transition">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-white min-h-screen shadow-md border-r">
            <nav class="mt-4">
                <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">Document Tasks</div>
                <a href="index.php" class="block py-2.5 px-4 text-sm bg-blue-100 text-blue-700 font-bold border-l-4 border-blue-700">
                    <i class="fas fa-search mr-2"></i> Document Tracking
                </a>
                <a href="receive.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50">
                    <i class="fas fa-file-import mr-2"></i> Receive
                </a>
                <a href="transfer.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50">
                    <i class="fas fa-exchange-alt mr-2"></i> Transfer
                </a>

                <?php if ($_SESSION['role'] === 'System Administrator'): ?>
                <div class="px-4 py-2 mt-6 text-xs font-bold text-gray-400 uppercase border-t pt-4">Administration</div>
                <a href="admin_roles.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-user-shield mr-2"></i> Roles</a>
                <a href="admin_users.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-users mr-2"></i> Users</a>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                
                <!-- Page Title -->
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-700">Document Audit Trail</h2>
                    <p class="text-sm text-gray-500">View the history and movement of documents across departments.</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    
                    <!-- Action Bar / Tabs -->
                    <div class="bg-blue-900 p-1 flex items-center text-white">
                        <div class="px-6 py-2 bg-white text-blue-900 font-bold rounded-t-sm text-xs uppercase tracking-widest">
                            <i class="fas fa-history mr-2"></i> Tracking Logs
                        </div>
                        <div class="px-6 py-2 hover:bg-blue-800 cursor-pointer text-xs font-bold uppercase tracking-widest text-blue-200">
                            <i class="fas fa-file-alt mr-2"></i> Detailed View
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="p-4 bg-gray-50 border-b flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Classification</label>
                            <select class="w-full border border-gray-300 p-2 rounded text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                <option>Active Files</option>
                                <option>Archived</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Category</label>
                            <select class="w-full border border-gray-300 p-2 rounded text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                <option>All Categories</option>
                                <option>Memo</option>
                                <option>Legal</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Filter Date</label>
                            <input type="date" class="w-full border border-gray-300 p-1.5 rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="flex gap-2">
                            <button class="bg-blue-700 text-white px-5 py-2 rounded text-sm font-bold hover:bg-blue-800 transition shadow-sm">
                                <i class="fas fa-search mr-1"></i> VIEW
                            </button>
                            <button class="bg-gray-200 text-gray-600 px-5 py-2 rounded text-sm font-bold hover:bg-gray-300 transition">
                                <i class="fas fa-sync-alt mr-1"></i> RESET
                            </button>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                    <th class="p-4">Log Date</th>
                                    <th class="p-4">Document ID</th>
                                    <th class="p-4">Type</th>
                                    <th class="p-4">Subject</th>
                                    <th class="p-4">Sender</th>
                                    <th class="p-4">Recipient</th>
                                    <th class="p-4 text-center">Document Age</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php
                                $sql = "SELECT 
                                            l.log_time, 
                                            d.document_number, 
                                            d.document_type, 
                                            d.title, 
                                            d.created_at as doc_birthday,
                                            u1.first_name as up_f, u1.last_name as up_l,
                                            u2.first_name as rec_f, u2.last_name as rec_l
                                        FROM document_logs l
                                        JOIN documents d ON l.document_id = d.id
                                        JOIN users u1 ON l.user_id = u1.id
                                        LEFT JOIN users u2 ON l.r_id = u2.id
                                        ORDER BY l.log_time DESC";

                                $result = $conn->query($sql);

                                if ($result && $result->num_rows > 0):
                                    while($row = $result->fetch_assoc()): 
                                        // Calculate Age
                                        $bday = new DateTime($row['doc_birthday']);
                                        $today = new DateTime();
                                        $diff = $bday->diff($today);
                                        $age = $diff->format('%ad %hh'); // Example: 2d 5h
                                ?>
                                <tr class="hover:bg-blue-50/50 transition-colors group">
                                    <td class="p-4">
                                        <div class="text-xs font-bold text-gray-700"><?php echo date('M d, Y', strtotime($row['log_time'])); ?></div>
                                        <div class="text-[10px] text-gray-400"><?php echo date('h:i A', strtotime($row['log_time'])); ?></div>
                                    </td>
                                    <td class="p-4">
                                        <span class="font-mono text-blue-700 font-bold bg-blue-50 px-2 py-1 border border-blue-100 rounded text-xs">
                                            <?php echo $row['document_number']; ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-xs font-medium text-gray-500">
                                        <?php echo $row['document_type']; ?>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-gray-800 text-xs uppercase truncate max-w-[200px]">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </div>
                                    </td>
                                    <td class="p-4 text-xs">
                                        <i class="fas fa-user-circle text-gray-300 mr-1"></i>
                                        <?php echo htmlspecialchars($row['up_f'] . " " . $row['up_l']); ?>
                                    </td>
                                    <td class="p-4 text-xs">
                                        <?php if ($row['rec_f']): ?>
                                            <i class="fas fa-arrow-right text-blue-300 mx-1 text-[10px]"></i>
                                            <?php echo htmlspecialchars($row['rec_f'] . " " . $row['rec_l']); ?>
                                        <?php else: ?>
                                            <span class="text-gray-300 italic">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="inline-block bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-[10px] font-bold border border-gray-200">
                                            <i class="far fa-clock mr-1"></i> <?php echo $age; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-gray-400 italic">
                                        <i class="fas fa-search text-4xl mb-3 block opacity-20"></i>
                                        No document logs found.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Success Popup (SweetAlert) -->
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <script>
        Swal.fire({
            title: 'Transfer Successful!',
            text: 'Document has been sent to <?php echo htmlspecialchars($_GET['recipient'] ?? 'the recipient'); ?>.',
            icon: 'success',
            confirmButtonColor: '#1e3a8a',
            confirmButtonText: 'Great!',
            timer: 5000,
            timerProgressBar: true,
            showClass: { popup: 'animate__animated animate__fadeInDown' },
            hideClass: { popup: 'animate__animated animate__fadeOutUp' }
        }).then(() => {
            const url = new URL(window.location);
            url.searchParams.delete('status');
            url.searchParams.delete('recipient');
            window.history.replaceState({}, document.title, url);
        });
    </script>
    <?php endif; ?> 

</body>
</html>
<?php 
ob_end_flush(); 
?>