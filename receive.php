<?php
// 1. Start output buffering to prevent "headers already sent" errors
ob_start();
session_start();

// 2. Security Check: Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 3. Database Connection
require_once 'config.php';

// 4. Get Filter Parameters
$search_code = isset($_GET['doc_code']) ? trim($_GET['doc_code']) : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : ''; 
$current_user_id = (int)$_SESSION['user_id'];

// 5. Build the Dynamic Query
// We fetch documents where the logged-in user is the current_holder
$query = "SELECT d.*, u.first_name, u.last_name 
          FROM documents d 
          JOIN users u ON d.uploaded_by = u.id 
          WHERE d.current_holder = ?";

$params = [$current_user_id];
$types = "i";

// Apply Search Filter (Number or Title)
if ($search_code !== '') {
    $query .= " AND (d.document_number LIKE ? OR d.title LIKE ?)";
    $searchTerm = "%$search_code%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

// Apply Date Filter
if ($filter_date !== '') {
    $query .= " AND DATE(d.created_at) = ?";
    $params[] = $filter_date;
    $types .= "s";
}

// Add Sorting
$query .= " ORDER BY d.created_at DESC";

// 6. Execute Prepared Statement
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Critical Database Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receive Document | PRC DTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                Welcome, <span class="font-bold text-blue-700"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span> 
                <span class="text-gray-400 mx-1">|</span>
                <span class="text-xs uppercase font-medium bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($_SESSION['role'] ?? 'Staff'); ?></span>
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
                <a href="index.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50 transition">
                    <i class="fas fa-search mr-2"></i> Document Tracking
                </a>
                <a href="receive.php" class="block py-2.5 px-4 text-sm bg-blue-100 text-blue-700 font-bold border-l-4 border-blue-700">
                    <i class="fas fa-file-import mr-2"></i> Receive
                </a>
                <a href="transfer.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50 transition">
                    <i class="fas fa-exchange-alt mr-2"></i> Transfer
                </a>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'System Administrator'): ?>
                <div class="px-4 py-2 mt-6 text-xs font-bold text-gray-400 uppercase border-t pt-4">Administration</div>
                <a href="admin_roles.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-user-shield mr-2"></i> Roles</a>
                <a href="admin_users.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-users mr-2"></i> Users</a>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="max-w-6xl mx-auto">
                
                <div class="mb-6 flex justify-between items-end">
                    <div>
                        <h2 class="text-xl font-bold text-gray-700">Incoming Documents</h2>
                        <p class="text-sm text-gray-500">Review and acknowledge documents currently assigned to you.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Server Time</p>
                        <p class="text-lg font-mono font-bold text-blue-900"><?php echo date('h:i A'); ?></p>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    
                    <!-- Action Bar -->
                    <div class="bg-blue-900 p-3 flex justify-between items-center text-white">
                        <div class="flex items-center gap-4 text-xs font-bold">
                            <span class="bg-blue-800 px-3 py-1 rounded flex items-center gap-2 italic">
                                <i class="fas fa-inbox"></i> INCOMING QUEUE
                            </span>
                        </div>
                        <a href="receive.php" class="text-xs bg-white/10 hover:bg-white/20 px-3 py-1 rounded transition flex items-center gap-2">
                            <i class="fas fa-sync-alt"></i> REFRESH LIST
                        </a>
                    </div>

                    <div class="p-6">
                        <!-- Search & Filter Form -->
                        <form method="GET" action="receive.php" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Filter by Date</label>
                                <input type="date" name="filter_date" 
                                       value="<?php echo htmlspecialchars($filter_date); ?>" 
                                       class="w-full border border-gray-300 p-2 rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                       onchange="this.form.submit()">
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Search Document</label>
                                <div class="flex gap-2">
                                    <input type="text" name="doc_code" value="<?php echo htmlspecialchars($search_code); ?>" 
                                           class="flex-1 border border-gray-300 p-2 rounded text-sm focus:ring-2 focus:ring-blue-500 outline-none" 
                                           placeholder="Enter Document Number or Subject Title...">
                                    
                                    <button type="submit" class="bg-blue-700 text-white px-6 py-2 rounded text-sm font-bold hover:bg-blue-800 transition shadow-sm">
                                        <i class="fas fa-search mr-1"></i> SEARCH
                                    </button>

                                    <?php if ($search_code !== '' || $filter_date !== ''): ?>
                                        <a href="receive.php" class="bg-gray-200 text-gray-600 px-4 py-2 rounded text-sm font-bold hover:bg-gray-300 transition flex items-center" title="Clear Filters">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>

                        <!-- Document Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                        <th class="pb-4 pl-2">Uploaded</th>
                                        <th class="pb-4">Document ID</th>
                                        <th class="pb-4">Subject & Description</th>
                                        <th class="pb-4">Sender</th>
                                        <th class="pb-4 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                        <tr class="hover:bg-blue-50/50 transition-colors group">
                                            <td class="py-4 pl-2">
                                                <div class="text-sm font-bold text-gray-700"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></div>
                                                <div class="text-[10px] text-gray-400"><?php echo date('h:i A', strtotime($row['created_at'])); ?></div>
                                            </td>
                                            <td class="py-4">
                                                <span class="font-mono text-blue-700 font-bold bg-blue-50 px-2 py-1 border border-blue-100 rounded text-xs">
                                                    <?php echo htmlspecialchars($row['document_number']); ?>
                                                </span>
                                            </td>
                                            <td class="py-4">
                                                <div class="font-bold text-gray-800 text-sm uppercase"><?php echo htmlspecialchars($row['title']); ?></div>
                                                <div class="text-xs text-gray-500 truncate max-w-xs italic"><?php echo htmlspecialchars($row['description'] ?? 'No description'); ?></div>
                                            </td>
                                            <td class="py-4">
                                                <div class="flex items-center gap-2">
                                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">
                                                        <?php echo strtoupper(substr($row['first_name'], 0, 1)); ?>
                                                    </div>
                                                    <span class="text-xs font-medium text-gray-600">
                                                        <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="py-4 text-center">
                                                <!-- IMPORTANT: Verify your file is named exactly download_and_receive.php -->
                                                <a href="download_and_receive.php?id=<?php echo $row['id']; ?>" 
                                                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-bold text-[11px] inline-flex items-center gap-2 shadow-sm transition-all active:scale-95">
                                                    <i class="fas fa-cloud-download-alt"></i> RECEIVE & DOWNLOAD
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="py-20 text-center">
                                                <div class="flex flex-col items-center">
                                                    <div class="bg-gray-50 h-16 w-16 rounded-full flex items-center justify-center mb-4">
                                                        <i class="fas fa-folder-open text-gray-300 text-3xl"></i>
                                                    </div>
                                                    <p class="text-gray-400 font-medium">No incoming documents found.</p>
                                                    <p class="text-xs text-gray-300 mt-1">Check back later or adjust your search.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
<?php 
// Close buffering
ob_end_flush(); 
?>