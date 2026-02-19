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
    <title>Document Tracking - PRCO1</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">

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
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white min-h-screen shadow-md">
            <nav class="mt-4">
                <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">Document Tasks</div>
                <a href="index.php" class="block py-2.5 px-4 text-sm bg-blue-100 text-blue-700 font-bold border-l-4 border-blue-700">
                    <i class="fas fa-search mr-2"></i> Document Tracking
                </a>
                <a href="receive.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-file-import mr-2"></i> Receive</a>
                <a href="transfer.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-exchange-alt mr-2"></i> Transfer</a>

                <!-- 2. CONDITIONAL ADMIN SIDEBAR -->
                <?php if ($_SESSION['role'] === 'System Administrator'): ?>
                <div class="px-4 py-2 mt-6 text-xs font-bold text-gray-400 uppercase border-t pt-4">Administration</div>
                <a href="admin_roles.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-user-shield mr-2"></i> Roles</a>
                <a href="admin_users.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-users mr-2"></i> Users</a>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div class="bg-white shadow rounded-sm overflow-hidden">
                <!-- Tabs -->
                <div class="bg-blue-500 flex text-white text-sm">
                    <div class="px-4 py-2 bg-blue-700 font-bold">Document Tracking</div>
                    <div class="px-4 py-2 hover:bg-blue-600 cursor-pointer">Document Details</div>
                </div>

                <!-- Filters -->
                <div class="p-4 grid grid-cols-4 gap-4 bg-gray-50 border-b text-sm">
                    <div>
                        <label class="block text-gray-600">Classification:</label>
                        <select class="w-full border p-1 rounded"><option>Active Files</option></select>
                    </div>
                    <div>
                        <label class="block text-gray-600">Category:</label>
                        <select class="w-full border p-1 rounded"><option>All</option></select>
                    </div>
                    <div>
                        <label class="block text-gray-600">Date From:</label>
                        <input type="date" class="w-full border p-1 rounded" value="2022-10-13">
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="bg-white border px-4 py-1 rounded shadow-sm text-blue-700 font-bold"><i class="fas fa-search"></i> View</button>
                        <button class="bg-white border px-4 py-1 rounded shadow-sm text-gray-600"><i class="fas fa-sync"></i> Reset</button>
                    </div>
                </div>

                <!-- Table -->
                <table class="w-full text-left text-sm border-collapse border">
    <thead>
        <tr class="bg-blue-200 text-gray-700 uppercase text-xs font-bold">
            <th class="p-3 border">Date</th>
            <th class="p-3 border">Document Name</th>
            <th class="p-3 border">Document Type</th>
            <th class="p-3 border">Subject</th>
            <th class="p-3 border">Uploaded By</th>
            <th class="p-3 border">Received By</th>
            <th class="p-3 border">Age of Document</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // JOIN documents to get Code, Type, and Subject
        // JOIN users twice to get the names of the Uploader and the Receiver
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

        while($row = $result->fetch_assoc()): 
            // Calculate Age: difference between document creation and now
            $bday = new DateTime($row['doc_birthday']);
            $today = new DateTime();
            $diff = $bday->diff($today);
            $age = $diff->format('%yy %mm %dd');
        ?>
        <tr class="hover:bg-gray-50 border-b">
            <td class="p-3 border"><?php echo date('m/d/Y', strtotime($row['log_time'])); ?></td>
            <td class="p-3 border font-mono text-blue-600 font-bold"><?php echo $row['document_number']; ?></td>
            <td class="p-3 border"><?php echo $row['document_type']; ?></td>
            <td class="p-3 border font-semibold"><?php echo $row['title']; ?></td>
            <td class="p-3 border"><?php echo $row['up_f'] . " " . $row['up_l']; ?></td>
            <td class="p-3 border"><?php echo $row['rec_f'] ? ($row['rec_f'] . " " . $row['rec_l']) : '<span class="text-gray-400">N/A</span>'; ?></td>
            <td class="p-3 border text-gray-500 font-bold"><?php echo $age; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
            </div>
        </main>
    </div>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
<script>
    Swal.fire({
        title: 'Transfer Successful!',
        text: 'Document has been sent to <?php echo htmlspecialchars($_GET['recipient']); ?>.',
        icon: 'success',
        confirmButtonColor: '#1e3a8a', // Matching your blue-900 theme
        confirmButtonText: 'Great!',
        timer: 5000, // Automatically closes after 5 seconds
        timerProgressBar: true,
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    }).then((result) => {
        // This removes the parameters from the URL without refreshing the page
        // so the popup doesn't appear again if the user refreshes manually.
        const url = new URL(window.location);
        url.searchParams.delete('status');
        url.searchParams.delete('recipient');
        window.history.replaceState({}, document.title, url);
    });
</script>
<?php endif; ?> 
</body>
</html>