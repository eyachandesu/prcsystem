<?php 
// 1. Start output buffering to prevent "headers already sent" errors
ob_start(); 
session_start();

// 2. Include database configuration
require_once 'config.php'; 

// 3. Security Check: Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

// 4. Fetch users for the recipient dropdown (excluding the current user)
$current_user_id = (int)$_SESSION['user_id'];
$users_query = "SELECT id, first_name, last_name, department, email FROM users WHERE id != $current_user_id ORDER BY last_name ASC";
$users = $conn->query($users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload & Send Document | PRC DTS</title>
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
                <a href="index.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50">
                    <i class="fas fa-search mr-2"></i> Document Tracking
                </a>
                <a href="receive.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50">
                    <i class="fas fa-file-import mr-2"></i> Receive
                </a>
                <a href="transfer.php" class="block py-2.5 px-4 text-sm bg-blue-100 text-blue-700 font-bold border-l-4 border-blue-700">
                    <i class="fas fa-exchange-alt mr-2"></i> Transfer
                </a>

                <!-- Conditional Administration Sidebar -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'System Administrator'): ?>
                <div class="px-4 py-2 mt-6 text-xs font-bold text-gray-400 uppercase border-t pt-4">Administration</div>
                <a href="admin_roles.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50">
                    <i class="fas fa-user-shield mr-2"></i> Roles
                </a>
                <a href="admin_users.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50">
                    <i class="fas fa-users mr-2"></i> Users
                </a>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-8">
            <div class="max-w-5xl mx-auto">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-700">Upload & Transfer Document</h2>
                    <p class="text-sm text-gray-500">Initiate a new document trail by uploading a file and selecting a recipient.</p>
                </div>

                <form action="process_transfer.php" method="POST" enctype="multipart/form-data" class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    
                    <!-- Action Bar -->
                    <div class="bg-blue-900 p-3 flex items-center gap-3 text-white">
                        <button type="submit" class="bg-white text-blue-900 px-6 py-2 rounded font-bold hover:bg-blue-50 transition flex items-center gap-2 shadow-md">
                            <i class="fas fa-paper-plane"></i> UPLOAD & SEND
                        </button>
                        <button type="reset" class="hover:bg-blue-800 px-4 py-2 rounded flex items-center gap-2 transition text-blue-100">
                            <i class="fas fa-undo"></i> RESET FORM
                        </button>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-10">
                        <!-- Left Side: Document Info -->
                        <div class="space-y-5">
                            <h3 class="font-bold border-b pb-2 text-blue-800 uppercase text-xs tracking-widest flex items-center">
                                <i class="fas fa-info-circle mr-2"></i> 1. Document Information
                            </h3>
                            
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Subject / Title*</label>
                                <input type="text" name="title" required 
                                    class="w-full border border-gray-300 p-2.5 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                                    placeholder="e.g. Request for IT Maintenance">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Document Type</label>
                                    <select name="doc_type" class="w-full border border-gray-300 p-2.5 rounded text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500">
                                        <option>Memo</option>
                                        <option>General</option>
                                        <option>Report</option>
                                        <option>Legal Document</option>                                 
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Select File (PDF/Image)*</label>
                                    <input type="file" name="doc_file" required 
                                        class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Description</label>
                                <textarea name="description" 
                                    class="w-full border border-gray-300 p-2.5 h-24 rounded text-sm outline-none focus:ring-2 focus:ring-blue-500" 
                                    placeholder="Briefly describe the document contents..."></textarea>
                            </div>
                        </div>

                        <!-- Right Side: Routing -->
                        <div class="space-y-5">
                            <h3 class="font-bold border-b pb-2 text-blue-800 uppercase text-xs tracking-widest flex items-center">
                                <i class="fas fa-route mr-2"></i> 2. Routing & Remarks
                            </h3>
                            
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Recipient (Existing Personnel)*</label>
                                <select name="to_user" required 
                                    class="w-full border border-gray-300 p-2.5 rounded text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Search Recipient --</option>
                                    <?php while($u = $users->fetch_assoc()): ?>
                                        <option value="<?php echo $u['id']; ?>">
                                            <?php echo htmlspecialchars("{$u['last_name']}, {$u['first_name']} ({$u['department']})"); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Action Required / Remarks</label>
                                <textarea name="remarks" 
                                    class="w-full border border-gray-300 p-2.5 h-40 rounded text-sm outline-none focus:ring-2 focus:ring-blue-500" 
                                    placeholder="Instructions for the recipient..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
<?php 
// 5. End buffering and flush output
ob_end_flush(); 
?>