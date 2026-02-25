<?php
ob_start();
session_start();

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php'; 
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
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <!-- Header -->
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
                Welcome, <span class="font-bold text-blue-700"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span> 
                <span class="text-gray-400 mx-1">|</span>
                <span class="text-xs uppercase font-medium bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($_SESSION['role'] ?? 'Staff'); ?></span>
            </div>
            <a href="logout.php" class="ml-4 text-red-600 font-bold"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
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
                <a href="admin_departments.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-building mr-2"></i> Departments</a>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-700">Document Tracking</h2>
                    <p class="text-sm text-gray-500">Monitor the current location and status of all documents.</p>
                </div>

                <div class="bg-white shadow rounded-sm overflow-hidden border">
                    <!-- Tabs -->
                    <div class="bg-blue-900 flex text-white text-[11px] font-bold uppercase tracking-wider">
                        <div class="px-6 py-3 bg-white text-blue-900">Document Logs</div>
                        <div class="px-6 py-3 hover:bg-blue-800 cursor-pointer text-blue-200">History Details</div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="p-4 grid grid-cols-4 gap-4 bg-gray-50 border-b text-sm">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Classification</label>
                            <select class="w-full border p-2 rounded bg-white outline-none focus:ring-1 focus:ring-blue-500"><option>Active Files</option></select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Category</label>
                            <select class="w-full border p-2 rounded bg-white outline-none focus:ring-1 focus:ring-blue-500"><option>All</option></select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Filter Date</label>
                            <input type="date" class="w-full border p-2 rounded outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="flex items-end gap-2">
                            <button class="bg-blue-700 text-white px-4 py-2 rounded font-bold text-xs uppercase hover:bg-blue-800 shadow-sm transition-all"><i class="fas fa-search mr-1"></i> View</button>
                            <button class="bg-white border px-4 py-2 rounded text-gray-600 font-bold text-xs uppercase hover:bg-gray-100 transition-all"><i class="fas fa-sync-alt mr-1"></i> Reset</button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase font-bold border-b">
                                    <th class="p-4 border-r">Log Date</th>
                                    <th class="p-4 border-r">Document ID</th>
                                    <th class="p-4 border-r">Subject</th>
                                    <th class="p-4 border-r">Sender</th>
                                    <th class="p-4 border-r">Recipient</th>
                                    <th class="p-4 border-r">Status</th>
                                    <th class="p-4 text-center">Age</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // JOIN logic to get Sender (u1) and Recipient (u2) based on current_holder
                                $sql = "SELECT 
                                            l.log_time, 
                                            d.document_number, 
                                            d.title, 
                                            d.current_status,
                                            d.created_at as doc_birthday,
                                            u1.first_name as send_f, u1.last_name as send_l,
                                            u2.first_name as rec_f, u2.last_name as rec_l
                                        FROM document_logs l
                                        JOIN documents d ON l.document_id = d.id
                                        JOIN users u1 ON l.user_id = u1.id
                                        LEFT JOIN users u2 ON d.current_holder = u2.id
                                        ORDER BY l.log_time DESC";

                                $result = $conn->query($sql);

                                if ($result && $result->num_rows > 0):
                                    while($row = $result->fetch_assoc()): 
                                        $bday = new DateTime($row['doc_birthday']);
                                        $today = new DateTime();
                                        $age = $bday->diff($today)->format('%ad %hh');
                                ?>
                                <tr class="hover:bg-blue-50 border-b transition-colors">
                                    <td class="p-4 border-r">
                                        <div class="font-bold text-gray-700"><?php echo date('m/d/Y', strtotime($row['log_time'])); ?></div>
                                        <div class="text-[10px] text-gray-400"><?php echo date('h:i A', strtotime($row['log_time'])); ?></div>
                                    </td>
                                    <td class="p-4 border-r font-mono text-blue-700 font-bold"><?php echo $row['document_number']; ?></td>
                                    <td class="p-4 border-r font-bold text-gray-800 uppercase"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="p-4 border-r"><?php echo htmlspecialchars($row['send_f'] . " " . $row['send_l']); ?></td>
                                    <td class="p-4 border-r font-medium text-blue-600">
                                        <?php echo $row['rec_f'] ? htmlspecialchars($row['rec_f'] . " " . $row['rec_l']) : '<span class="text-gray-300">N/A</span>'; ?>
                                    </td>
                                    <td class="p-4 border-r uppercase font-bold text-[10px]">
                                        <?php 
                                            $statusClass = $row['current_status'] == 'Under Review' ? 'text-orange-600' : 'text-green-600';
                                            echo "<span class='$statusClass'>{$row['current_status']}</span>"; 
                                        ?>
                                    </td>
                                    <td class="p-4 text-center font-bold text-gray-500 italic"><?php echo $age; ?></td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="7" class="p-10 text-center text-gray-400">No documents tracked yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Success Popup -->
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <script>
        Swal.fire({
            title: 'Transfer Successful!',
            text: 'Document has been sent to <?php echo htmlspecialchars($_GET['recipient'] ?? 'the recipient'); ?>.',
            icon: 'success',
            confirmButtonColor: '#1e3a8a'
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
<?php ob_end_flush(); ?>