<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); 

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if ($_SESSION['role'] !== 'System Administrator') { header("Location: index.php?error=unauthorized"); exit(); }

include 'config.php'; 

// --- HANDLE STATUS TOGGLE ---
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $new_status = ($_GET['toggle_status'] == 'active') ? 'inactive' : 'active';
    
    $stmt = $conn->prepare("UPDATE departments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
    header("Location: admin_departments.php?msg=Department status updated.");
    exit();
}

// --- HANDLE DELETION ---
if (isset($_GET['delete_id'])) {
    $id_to_delete = $_GET['delete_id'];
    $delete_stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $delete_stmt->bind_param("i", $id_to_delete);
    $delete_stmt->execute();
    header("Location: admin_departments.php?msg=Department deleted successfully");
    exit();
}

$dept_result = $conn->query("SELECT * FROM departments ORDER BY dept_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Departments | PRC DTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <header class="bg-white border-b shadow-sm p-3 flex justify-between items-center px-6">
        <div class="flex items-center">
            <img src="prclogo.png" alt="PRC Logo" class="h-10 w-10 mr-4">
            <div>
                <h1 class="text-blue-900 font-bold text-lg leading-tight">Document Tracking System</h1>
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Administration Panel</p>
            </div>
        </div>
        <div class="text-sm">
            Admin: <span class="font-bold text-blue-700"><?php echo $_SESSION['name']; ?></span> | 
            <a href="index.php" class="text-blue-600 font-bold hover:underline">Exit Admin</a>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white min-h-screen shadow-md border-r">
            <nav class="mt-4">
                <div class="px-4 py-2 mt-6 text-xs font-semibold text-gray-400 uppercase border-t pt-4 tracking-widest">Administration</div>
                <a href="admin_roles.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50">
                    <i class="fas fa-user-shield mr-2"></i> Roles & Permissions
                </a>
                <a href="admin_users.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50">
                    <i class="fas fa-users mr-2"></i> User Management
                </a>
                <a href="admin_departments.php" class="block py-2.5 px-4 text-sm bg-blue-100 text-blue-800 border-l-4 border-blue-800 font-bold">
                    <i class="fas fa-building mr-2"></i> Departments
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <?php if(isset($_GET['msg'])): ?>
                <div class="bg-green-100 text-green-700 p-3 mb-4 rounded border border-green-200 text-sm">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-blue-900">Department Management</h2>
                    <p class="text-sm text-gray-500">Add or edit organizational units within the system.</p>
                </div>
                <button onclick="openModal('add')" class="bg-blue-900 text-white px-4 py-2 rounded shadow-lg hover:bg-blue-800 transition active:scale-95">
                    <i class="fas fa-plus-circle mr-2"></i> Add Department
                </button>
            </div>

            <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-400 text-[11px] font-bold uppercase tracking-wider">
                        <tr>
                            <th class="p-4">Department Name</th>
                            <th class="p-4">Code</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-gray-700">
                        <?php if($dept_result->num_rows > 0): ?>
                            <?php while($d = $dept_result->fetch_assoc()): ?>
                            <tr class="hover:bg-blue-50/30 transition">
                                <td class="p-4 font-bold text-blue-900"><?php echo htmlspecialchars($d['dept_name']); ?></td>
                                <td class="p-4 font-mono text-gray-500"><?php echo htmlspecialchars($d['dept_code']); ?></td>
                                <td class="p-4">
                                    <?php if($d['status'] == 'active'): ?>
                                        <span class="text-green-600 font-bold text-[10px] uppercase bg-green-50 px-2 py-1 rounded">Active</span>
                                    <?php else: ?>
                                        <span class="text-red-500 font-bold text-[10px] uppercase bg-red-50 px-2 py-1 rounded">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-right flex justify-end gap-3">
                                    <a href="?toggle_status=<?php echo $d['status']; ?>&id=<?php echo $d['id']; ?>" class="<?php echo ($d['status']=='active') ? 'text-orange-400' : 'text-green-500'; ?> hover:scale-110 transition">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <button onclick='openModal("edit", <?php echo json_encode($d); ?>)' class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete_id=<?php echo $d['id']; ?>" onclick="return confirm('Delete this department?')" class="text-red-400 hover:text-red-600">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="p-10 text-center text-gray-400 italic">No departments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Form -->
    <div id="deptModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden animate__animated animate__zoomIn animate__faster">
            <form action="process_department.php" method="POST">
                <input type="hidden" name="dept_id" id="modal_dept_id">
                <div class="bg-blue-900 p-4 text-white flex justify-between items-center">
                    <h3 class="font-bold uppercase tracking-widest text-sm" id="modal_title">Add Department</h3>
                    <button type="button" onclick="closeModal()" class="text-2xl hover:text-gray-300">&times;</button>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1 block">Full Department Name</label>
                        <input type="text" name="dept_name" id="modal_dept_name" required 
                               class="border-b-2 border-gray-200 p-2 w-full text-sm outline-none focus:border-blue-900 transition-colors bg-gray-50"
                               placeholder="e.g., Licensure Office">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1 block">Short Code / Abbreviation</label>
                        <input type="text" name="dept_code" id="modal_dept_code" required 
                               class="border-b-2 border-gray-200 p-2 w-full text-sm outline-none focus:border-blue-900 transition-colors bg-gray-50"
                               placeholder="e.g., LIC-OFF">
                    </div>
                </div>
                <div class="p-4 bg-gray-50 flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-xs font-bold text-gray-500 uppercase">Cancel</button>
                    <button type="submit" class="bg-blue-900 text-white px-6 py-2 rounded font-bold text-xs uppercase shadow-md hover:bg-blue-800" id="modal_submit_btn">
                        Save Department
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(mode, data = null) {
            const modal = document.getElementById('deptModal');
            const title = document.getElementById('modal_title');
            const submitBtn = document.getElementById('modal_submit_btn');
            
            if (mode === 'edit') {
                title.innerText = "Edit Department";
                submitBtn.innerText = "Update Changes";
                document.getElementById('modal_dept_id').value = data.id;
                document.getElementById('modal_dept_name').value = data.dept_name;
                document.getElementById('modal_dept_code').value = data.dept_code;
            } else {
                title.innerText = "Add New Department";
                submitBtn.innerText = "Create Department";
                document.getElementById('modal_dept_id').value = "";
                document.querySelector('#deptModal form').reset();
            }
            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('deptModal').classList.add('hidden');
        }
    </script>
</body>
</html>