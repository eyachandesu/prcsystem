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
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
    header("Location: admin_users.php?msg=User status updated to " . $new_status);
    exit();
}

// --- HANDLE DELETION ---
if (isset($_GET['delete_id'])) {
    $id_to_delete = $_GET['delete_id'];
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

$users_result = $conn->query("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.last_name ASC");
$roles_array = []; // Store roles for JS and dropdowns
$roles_result = $conn->query("SELECT id, role_name FROM roles");
while($r = $roles_result->fetch_assoc()) { $roles_array[] = $r; }
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
                <h1 class="text-blue-900 font-bold text-lg">Document Tracking System</h1>
                <p class="text-[10px] font-semibold text-gray-500 uppercase">Administration Panel</p>
            </div>
        </div>
        <div class="text-sm">Admin: <span class="font-bold"><?php echo $_SESSION['name']; ?></span> | <a href="index.php" class="text-blue-600 font-bold hover:underline">Exit Admin</a></div>
    </header>

    <div class="flex">
        <aside class="w-64 bg-white min-h-screen shadow-md border-r">
            <nav class="mt-4">
                <div class="px-4 py-2 mt-6 text-xs font-semibold text-gray-400 uppercase border-t pt-4">Administration</div>
                <a href="admin_roles.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50"><i class="fas fa-user-shield mr-2"></i> Roles & Permissions</a>
                <a href="admin_users.php" class="block py-2.5 px-4 text-sm bg-blue-100 text-blue-800 border-l-4 border-blue-800 font-bold"><i class="fas fa-users mr-2"></i> User Management</a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <?php if(isset($_GET['msg'])): ?>
                <div class="bg-green-100 text-green-700 p-3 mb-4 rounded border border-green-200 text-sm"><i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?></div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-blue-900">User Management</h2>
                <button onclick="openModal('add')" class="bg-blue-800 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
                    <i class="fas fa-user-plus mr-2"></i> Create New User
                </button>
            </div>

            <div class="bg-white rounded border shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-600 font-bold">
                        <tr>
                            <th class="p-4">Full Name</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Department</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-gray-700">
                        <?php while($u = $users_result->fetch_assoc()): ?>
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="p-4 font-bold text-blue-900"><?php echo $u['last_name'] . ', ' . $u['first_name']; ?></td>
                            <td class="p-4"><?php echo $u['email']; ?></td>
                            <td class="p-4 text-xs"><?php echo $u['department']; ?></td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded bg-gray-100 text-gray-600 text-[10px] font-bold uppercase"><?php echo $u['role_name'] ?? 'No Role'; ?></span>
                            </td>
                            <td class="p-4">
                                <?php if($u['status'] == 'active'): ?>
                                    <span class="text-green-600 font-bold text-[10px] uppercase"><i class="fas fa-circle text-[8px] mr-1"></i> Active</span>
                                <?php else: ?>
                                    <span class="text-red-500 font-bold text-[10px] uppercase"><i class="fas fa-circle text-[8px] mr-1"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right flex justify-end gap-3">
                                <!-- Status Toggle -->
                                <a href="?toggle_status=<?php echo $u['status']; ?>&id=<?php echo $u['id']; ?>" title="Toggle Status" class="<?php echo ($u['status']=='active') ? 'text-orange-400' : 'text-green-500'; ?>">
                                    <i class="fas fa-power-off"></i>
                                </a>
                                <!-- Edit Button -->
                                <button onclick='openModal("edit", <?php echo json_encode($u); ?>)' class="text-blue-500 hover:text-blue-700">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- Delete Button -->
                                <?php if($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete_id=<?php echo $u['id']; ?>" onclick="return confirm('Permanently delete this user?')" class="text-red-400 hover:text-red-600"><i class="fas fa-trash-alt"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Form (Handles both Add and Edit) -->
    <div id="userModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden">
            <form action="process_user.php" method="POST">
                <input type="hidden" name="user_id" id="modal_user_id">
                <div class="bg-blue-900 p-4 text-white flex justify-between">
                    <h3 class="font-bold" id="modal_title">Add New User</h3>
                    <button type="button" onclick="closeModal()">&times;</button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">First Name</label>
                            <input type="text" name="first_name" id="modal_first_name" required class="border p-2 w-full rounded text-sm outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Last Name</label>
                            <input type="text" name="last_name" id="modal_last_name" required class="border p-2 w-full rounded text-sm outline-none focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Email Address</label>
                        <input type="email" name="email" id="modal_email" required class="border p-2 w-full rounded text-sm outline-none focus:border-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Department</label>
                            <select name="department" id="modal_department" required class="border p-2 w-full rounded text-sm bg-white outline-none focus:border-blue-500">
                                <option value="Licensure Office">Licensure Office</option>
                                <option value="Legal Service">Legal Service</option>
                                <option value="ICT Service">ICT Service</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">System Role</label>
                            <select name="role_id" id="modal_role_id" required class="border p-2 w-full rounded text-sm bg-white outline-none focus:border-blue-500">
                                <?php foreach($roles_array as $role): ?>
                                    <option value="<?php echo $role['id']; ?>"><?php echo $role['role_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div id="password_section">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Password</label>
                        <input type="password" name="password" id="modal_password" placeholder="Leave blank to keep current" class="border p-2 w-full rounded text-sm outline-none focus:border-blue-500">
                        <p class="text-[9px] text-gray-400 mt-1 italic">* Password is only required when creating new accounts.</p>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 text-right">
                    <button type="submit" class="bg-blue-800 text-white px-6 py-2 rounded font-bold text-sm shadow-md" id="modal_submit_btn">Save Account</button>
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