<?php 
session_start(); 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'System Administrator') {
    header("Location: index.php?error=unauthorized_access");
    exit();
}

include 'config.php'; 

// Fetch all roles
$roles_result = $conn->query("SELECT * FROM roles ORDER BY id ASC");
// Fetch all permissions for the checkboxes
$all_permissions_result = $conn->query("SELECT * FROM permissions");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Roles Management | PRCO1 DTS</title>
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
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Management</div>
                <a href="admin_roles.php" class="block py-2.5 px-4 text-sm bg-blue-100 text-blue-800 border-l-4 border-blue-800 font-bold">
                    <i class="fas fa-user-shield mr-2"></i> Roles & Permissions
                </a>
                <a href="admin_users.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50">
                    <i class="fas fa-users mr-2"></i> User Management
                </a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <!-- Messages -->
            <?php if(isset($_GET['msg'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 text-sm shadow-sm">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-blue-900">Configure System Roles</h2>
                <button onclick="openRoleModal('add')" class="bg-blue-800 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
                    <i class="fas fa-plus mr-2"></i> Create New Role
                </button>
            </div>

            <div class="bg-white rounded border shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-600 font-bold">
                        <tr>
                            <th class="p-4">Role Title</th>
                            <th class="p-4">Description</th>
                            <th class="p-4">Permissions Attached</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php while($role = $roles_result->fetch_assoc()): ?>
                        <tr class="hover:bg-blue-50/20">
                            <td class="p-4 font-bold text-blue-900"><?php echo $role['role_name']; ?></td>
                            <td class="p-4 text-gray-500 text-xs"><?php echo $role['description']; ?></td>
                            <td class="p-4">
                                <?php 
                                    $rid = $role['id'];
                                    $res = $conn->query("SELECT p.perm_name FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = $rid");
                                    while($p = $res->fetch_assoc()) {
                                        echo "<span class='bg-blue-50 text-blue-600 px-2 py-0.5 rounded text-[9px] font-bold uppercase mr-1 border border-blue-100'>".$p['perm_name']."</span>";
                                    }
                                ?>
                            </td>
                            <td class="p-4 text-right">
                                <button onclick="openRoleModal('edit', <?php echo $role['id']; ?>, '<?php echo addslashes($role['role_name']); ?>', '<?php echo addslashes($role['description']); ?>')" class="text-blue-600 hover:text-blue-900 font-bold text-xs uppercase mr-3">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Permission Modal -->
    <div id="roleModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-lg w-full max-w-md shadow-2xl overflow-hidden">
            <form action="process_role.php" method="POST" id="roleForm">
                <input type="hidden" name="role_id" id="modal_role_id">
                
                <div class="bg-blue-900 p-4 text-white flex justify-between items-center">
                    <h3 class="font-bold text-sm uppercase tracking-wider" id="modal_title"><i class="fas fa-shield-alt mr-2"></i> Role Configuration</h3>
                    <button type="button" onclick="closeModal()" class="text-xl">&times;</button>
                </div>
                
                <div class="p-6">
                    <div class="mb-4">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Role Name</label>
                        <input type="text" name="role_name" id="modal_role_name" required class="w-full border-b p-2 font-bold text-blue-900 outline-none focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Description</label>
                        <input type="text" name="description" id="modal_description" class="w-full border-b p-2 text-sm text-gray-600 outline-none focus:border-blue-500">
                    </div>

                    <label class="text-[10px] font-bold text-gray-400 uppercase block mb-3">Allowed Actions</label>
                    <div class="grid grid-cols-2 gap-3 bg-gray-50 p-4 rounded border border-dashed">
                        <?php 
                        $all_permissions_result->data_seek(0);
                        while($p = $all_permissions_result->fetch_assoc()): 
                        ?>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="permissions[]" value="<?php echo $p['id']; ?>" id="p_<?php echo $p['id']; ?>" class="perm-checkbox w-4 h-4 rounded text-blue-600">
                            <label for="p_<?php echo $p['id']; ?>" class="text-[11px] font-semibold text-gray-700 cursor-pointer"><?php echo $p['perm_name']; ?></label>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="p-4 bg-gray-100 flex justify-end gap-3">
                    <button type="button" onclick="closeModal()" class="text-xs font-bold text-gray-500 uppercase">Cancel</button>
                    <button type="submit" class="bg-blue-800 text-white px-6 py-2 rounded text-xs font-bold uppercase shadow-lg">Save Configuration</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRoleModal(mode, id = '', name = '', desc = '') {
            document.getElementById('modal_role_id').value = id;
            document.getElementById('modal_role_name').value = name;
            document.getElementById('modal_description').value = desc;
            
            const checkboxes = document.querySelectorAll('.perm-checkbox');
            checkboxes.forEach(cb => cb.checked = false); // Clear checkboxes

            if (mode === 'add') {
                document.getElementById('modal_title').innerHTML = '<i class="fas fa-plus mr-2"></i> Create New Role';
            } else {
                document.getElementById('modal_title').innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Role Permissions';
        
            }
            
            document.getElementById('roleModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('roleModal').classList.add('hidden');
        }
    </script>
</body>
</html>