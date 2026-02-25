<?php 
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); 

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if ($_SESSION['role'] !== 'System Administrator') { header("Location: index.php?error=unauthorized"); exit(); }

include 'config.php'; 

// --- 1. HANDLE STATUS TOGGLE ---
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $new_status = ($_GET['toggle_status'] == 'active') ? 'inactive' : 'active';
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
    header("Location: admin_users.php?msg=User status updated to " . $new_status);
    exit();
}

// --- 2. HANDLE DELETION ---
if (isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];
    if ($id_to_delete == $_SESSION['user_id']) {
        header("Location: admin_users.php?error=You cannot delete your own account.");
        exit();
    }
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $id_to_delete);
    $delete_stmt->execute();
    header("Location: admin_users.php?msg=User deleted successfully");
    exit();
}

// --- 3. FETCH USERS ---
$users_result = $conn->query("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.last_name ASC");

// --- 4. FETCH ROLES FOR DROPDOWN ---
$roles_array = [];
$roles_query = $conn->query("SELECT id, role_name FROM roles ORDER BY role_name ASC");
while($r = $roles_query->fetch_assoc()) { $roles_array[] = $r; }

// --- 5. FETCH DEPARTMENTS FOR DROPDOWN ---
$depts_array = [];
// Check if departments table exists, otherwise pull distinct depts from users table
$dept_check = $conn->query("SHOW TABLES LIKE 'departments'");
if($dept_check->num_rows > 0) {
    $depts_query = $conn->query("SELECT dept_name FROM departments ORDER BY dept_name ASC");
} else {
    $depts_query = $conn->query("SELECT DISTINCT department as dept_name FROM users WHERE department IS NOT NULL ORDER BY department ASC");
}
while($d = $depts_query->fetch_assoc()) { $depts_array[] = $d['dept_name']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | PRCO1 DTS</title>
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
        <div class="text-sm">Admin: <span class="font-bold text-blue-700"><?php echo htmlspecialchars($_SESSION['name']); ?></span> | <a href="index.php" class="text-red-600 font-bold hover:underline">Exit Admin</a></div>
    </header>

    <div class="flex">
        <aside class="w-64 bg-white min-h-screen shadow-md border-r">
            <nav class="mt-4">
                <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-widest">Administration</div>
                <a href="admin_roles.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50 transition"><i class="fas fa-user-shield mr-2"></i> Roles & Permissions</a>
                <a href="admin_users.php" class="block py-2.5 px-4 text-sm bg-blue-100 text-blue-800 border-l-4 border-blue-800 font-bold"><i class="fas fa-users mr-2"></i> User Management</a>
                <a href="admin_departments.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50 transition"><i class="fas fa-building mr-2"></i> Departments</a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <!-- Notifications -->
            <?php if(isset($_GET['msg'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-6 text-sm flex items-center shadow-sm">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-6 text-sm flex items-center shadow-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-blue-900">User Accounts</h2>
                    <p class="text-sm text-gray-500">Manage system access and department assignments.</p>
                </div>
                <button onclick="openModal('add')" class="bg-blue-900 text-white px-5 py-2 rounded-lg shadow-lg hover:bg-blue-800 transition-all flex items-center gap-2 font-bold text-sm">
                    <i class="fas fa-user-plus"></i> CREATE NEW USER
                </button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-400 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="p-4">Full Name</th>
                            <th class="p-4">Email Address</th>
                            <th class="p-4">Department</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-gray-700">
                        <?php while($u = $users_result->fetch_assoc()): ?>
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="p-4 font-bold text-blue-900"><?php echo htmlspecialchars($u['last_name'] . ', ' . $u['first_name']); ?></td>
                            <td class="p-4 text-gray-500"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td class="p-4 text-xs font-semibold"><?php echo htmlspecialchars($u['department']); ?></td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-[9px] font-bold uppercase border border-blue-100">
                                    <?php echo htmlspecialchars($u['role_name'] ?? 'No Role'); ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <?php if($u['status'] == 'active'): ?>
                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase">Active</span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-400 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-3 items-center">
                                    <a href="?toggle_status=<?php echo $u['status']; ?>&id=<?php echo $u['id']; ?>" 
                                       title="Toggle Status" 
                                       class="<?php echo ($u['status']=='active') ? 'text-orange-400 hover:text-orange-600' : 'text-green-500 hover:text-green-700'; ?> transition">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <button onclick='openModal("edit", <?php echo json_encode($u); ?>)' class="text-blue-500 hover:text-blue-700 transition">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete_id=<?php echo $u['id']; ?>" onclick="return confirm('Are you sure you want to permanently delete this user?')" class="text-red-300 hover:text-red-600 transition"><i class="fas fa-trash-alt"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Form -->
    <div id="userModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-in">
            <form action="process_user.php" method="POST">
                <input type="hidden" name="user_id" id="modal_user_id">
                <div class="bg-blue-900 p-5 text-white flex justify-between items-center">
                    <h3 class="font-bold uppercase text-xs tracking-widest" id="modal_title">Add New User</h3>
                    <button type="button" onclick="closeModal()" class="text-2xl leading-none">&times;</button>
                </div>
                <div class="p-8 space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">First Name</label>
                            <input type="text" name="first_name" id="modal_first_name" required class="border border-gray-200 p-2.5 w-full rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Last Name</label>
                            <input type="text" name="last_name" id="modal_last_name" required class="border border-gray-200 p-2.5 w-full rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Email Address</label>
                        <input type="email" name="email" id="modal_email" required class="border border-gray-200 p-2.5 w-full rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="juan.delacruz@prc.gov.ph">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Department</label>
                            <select name="department" id="modal_department" required class="border border-gray-200 p-2.5 w-full rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="" disabled selected>-- Select --</option>
                                <?php foreach($depts_array as $dept): ?>
                                    <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">System Role</label>
                            <select name="role_id" id="modal_role_id" required class="border border-gray-200 p-2.5 w-full rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="" disabled selected>-- Select --</option>
                                <?php foreach($roles_array as $role): ?>
                                    <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div id="password_section">
                        <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Password</label>
                        <input type="password" name="password" id="modal_password" class="border border-gray-200 p-2.5 w-full rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <p class="text-[9px] text-gray-400 mt-2 italic">* Leave blank to keep current password when editing.</p>
                    </div>
                </div>
                <div class="p-6 bg-gray-50 border-t flex justify-end">
                    <button type="submit" class="bg-blue-900 text-white px-8 py-2.5 rounded-lg font-bold text-xs uppercase shadow-lg hover:bg-blue-800 transition-all active:scale-95" id="modal_submit_btn">Save Account</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(mode, data = null) {
            const modal = document.getElementById('userModal');
            const title = document.getElementById('modal_title');
            const submitBtn = document.getElementById('modal_submit_btn');
            
            if (mode === 'edit') {
                title.innerText = "Edit User Account";
                submitBtn.innerText = "Update Account";
                document.getElementById('modal_user_id').value = data.id;
                document.getElementById('modal_first_name').value = data.first_name;
                document.getElementById('modal_last_name').value = data.last_name;
                document.getElementById('modal_email').value = data.email;
                document.getElementById('modal_department').value = data.department;
                document.getElementById('modal_role_id').value = data.role_id;
                document.getElementById('modal_password').required = false;
            } else {
                title.innerText = "Add New User";
                submitBtn.innerText = "Create Account";
                document.getElementById('modal_user_id').value = "";
                document.querySelector('form').reset();
                document.getElementById('modal_password').required = true;
            }
            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }
    </script>
</body>
</html>
<?php ob_end_flush(); ?>