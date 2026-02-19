<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// 1. Get Filter Parameters
$search_code = isset($_GET['doc_code']) ? trim($_GET['doc_code']) : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : ''; // New date filter
$current_user_id = $_SESSION['user_id'];

// 2. Build the Query
// Update the query at the top of receive.php to include document_type
$query = "SELECT d.id, d.document_number, d.document_type, d.title, d.created_at, u.first_name, u.last_name 
          FROM documents d 
          JOIN users u ON d.uploaded_by = u.id 
          WHERE d.current_holder = ? 
          AND d.current_status IN ('Draft', 'Submitted', 'Forwarded', 'Pending')";

// 3. Apply Filters
$params = [$current_user_id];
$types = "i";

if ($search_code !== '') {
    $query .= " AND (d.document_number LIKE ? OR d.title LIKE ?)";
    $searchTerm = "%$search_code%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($filter_date !== '') {
    // DATE() function extracts the date part from the timestamp
    $query .= " AND DATE(d.created_at) = ?";
    $params[] = $filter_date;
    $types .= "s";
}

$query .= " ORDER BY d.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receive Document - PRC DTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

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
            Welcome, <span class="font-bold text-blue-700"><?php echo $_SESSION['name']; ?></span> 
            (<?php echo $_SESSION['role']; ?>)
            <a href="logout.php" class="ml-4 text-red-600 font-bold"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white min-h-screen shadow-md">
            <nav class="mt-4">
                <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">Document Tasks</div>
                <a href="index.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50">
                    <i class="fas fa-search mr-2"></i> Document Tracking
                </a>
                <a href="receive.php" class="block py-2.5 px-4 text-sm bg-blue-100 text-blue-700 font-bold border-l-4 border-blue-700">
                    <i class="fas fa-file-import mr-2"></i> Receive
                </a>
                <a href="transfer.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50">
                    <i class="fas fa-exchange-alt mr-2"></i> Transfer
                </a>
                <!-- 2. CONDITIONAL ADMIN SIDEBAR -->
                <?php if ($_SESSION['role'] === 'System Administrator'): ?>
                <div class="px-4 py-2 mt-6 text-xs font-bold text-gray-400 uppercase border-t pt-4">Administration</div>
                <a href="admin_roles.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-user-shield mr-2"></i> Roles</a>
                <a href="admin_users.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-users mr-2"></i> Users</a>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-4">
            <div class="bg-white shadow rounded-sm border">
                
                <!-- Action Bar -->
                <div class="bg-blue-500 p-1 flex items-center gap-1 text-white text-xs">
                    <div class="bg-blue-700 px-4 py-1.5 flex items-center gap-2"><i class="fas fa-list"></i> Incoming List</div>
                    <a href="receive.php" class="hover:bg-blue-600 px-4 py-1.5 flex items-center gap-2"><i class="fas fa-sync"></i> Refresh</a>
                </div>

                <div class="p-4">
                    <!-- Functional Filter Form -->
                    <form method="GET" action="receive.php" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Date with Calendar -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-gray-600 w-24">Filter Date</label>
                            <input type="date" name="filter_date" 
                                   value="<?php echo htmlspecialchars($filter_date); ?>" 
                                   class="border p-1 text-sm w-full border-blue-300 focus:ring-1 focus:ring-blue-500 outline-none"
                                   onchange="this.form.submit()"> <!-- Auto-submits when date is picked -->
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-gray-600 w-24">Current Time</label>
                            <input type="text" readonly class="border p-1 text-sm w-full bg-gray-100 text-gray-400" value="<?php echo date('h:i A'); ?>">
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-gray-600 w-32">Search</label>
                            <div class="relative w-full flex gap-1">
                                <input type="text" name="doc_code" value="<?php echo htmlspecialchars($search_code); ?>" 
                                       class="border p-1 text-sm w-full border-blue-300 focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                       placeholder="Code or Subject...">
                                
                                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded-sm hover:bg-blue-700">
                                    <i class="fas fa-search"></i>
                                </button>

                                <?php if ($search_code !== '' || $filter_date !== ''): ?>
                                    <a href="receive.php" class="bg-red-500 text-white px-3 py-1 rounded-sm hover:bg-red-600 flex items-center" title="Clear Filters">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <!-- Document Table -->
                    <div class="border rounded-sm overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 border-b text-gray-600 text-xs uppercase">
                                <tr>
                                    <th class="p-3 text-left">Date Uploaded</th>
                                    <th class="p-3 text-left">Document Name</th>
                                    <th class="p-3 text-left">Subject</th>
                                    <th class="p-3 text-left">Uploaded By</th>
                                    <th class="p-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="border-b hover:bg-blue-50 transition-colors">
                                        <td class="p-3 text-xs text-gray-600">
                                            <div class="font-bold"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></div>
                                            <div class="text-[10px] text-gray-400"><?php echo date('h:i A', strtotime($row['created_at'])); ?></div>
                                        </td>
                                        <td class="p-3">
                                            <span class="font-mono text-blue-700 font-bold bg-blue-50 px-2 py-0.5 border border-blue-100 rounded text-xs">
                                                <?php echo $row['document_number']; ?>
                                            </span>
                                        </td>
                                        <td class="p-3 font-semibold text-gray-800 uppercase text-xs">
                                            <?php echo $row['title']; ?>
                                        </td>
                                        <td class="p-3 text-gray-600 text-xs">
                                            <?php echo $row['first_name'] . ' ' . $row['last_name']; ?>
                                        </td>
                                        <td class="p-3 text-center">
                                            <!-- This link triggers the recording and the download -->
                                            <a href="download_and_receive.php?id=<?php echo $row['id']; ?>" 
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-sm text-[11px] font-bold inline-flex items-center gap-2 shadow-sm">
                                                <i class="fas fa-file-download"></i> Receive & Download
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="p-12 text-center">
                                            <i class="fas fa-calendar-times text-gray-200 text-5xl mb-3"></i>
                                            <p class="text-gray-400 italic font-bold">No documents found for the selected criteria.</p>
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
</body>
</html>