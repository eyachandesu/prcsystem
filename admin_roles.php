<?php 
session_start(); 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'System Administrator') {
    header("Location: index.php?error=unauthorized_access");
    exit();
}

include 'config.php'; 

// Fetch all roles - UUIDs don't sort well by ID, so we sort by name
$roles_result = $conn->query("SELECT * FROM roles ORDER BY role_name ASC");
// Fetch all permissions for the checkboxes
$all_permissions_result = $conn->query("SELECT * FROM permissions");

// Define the System Admin UUID from your SQL dump
$sys_admin_uuid = '550e8400-e29b-41d4-a716-446655440000';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Roles Management | PRCO1 DTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <div class="text-sm">Admin: <span class="font-bold"><?php echo htmlspecialchars($_SESSION['name']); ?></span> | <a href="index.php" class="text-blue-600 font-bold hover:underline">Exit Admin</a></div>
    </header>

    <div class="flex">
        <aside class="w-64 bg-white min-h-screen shadow-md border-r">
            <nav class="mt-4">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administration </div>
                <a href="admin_roles.php" class="block py-2.5 px-4 text-sm bg-blue-100 text-blue-800 border-l-4 border-blue-800 font-bold">
                    <i class="fas fa-user-shield mr-2"></i> Roles & Permissions
                </a>
                <a href="admin_users.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50 transition">
                    <i class="fas fa-users mr-2"></i> User Management
                </a>
                <a href="admin_departments.php" class="block py-2.5 px-4 text-sm text-gray-600 hover:bg-blue-50 transition">
                    <i class="fas fa-building mr-2"></i> Departments
                </a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <?php if(isset($_GET['msg'])): ?>
                <script>
                    Swal.fire({ icon: 'success', title: 'Success', text: '<?php echo htmlspecialchars($_GET['msg']); ?>', confirmButtonColor: '#1e3a8a' });
                </script>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-blue-900">System Roles</h2>
                    <p class="text-sm text-gray-500">Define what actions users can perform based on their role.</p>
                </div>
                <button onclick="openRoleModal('add')" class="bg-blue-800 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Create New Role
                </button>
            </div>

            <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-[11px] uppercase text-gray-400 font-bold tracking-wider">
                        <tr>
                            <th class="p-4">Role Title</th>
                            <th class="p-4">Description</th>
                            <th class="p-4">Permissions Attached</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while($role = $roles_result->fetch_assoc()): ?>
                        <tr class="hover:bg-blue-50/20 transition-colors">
                            <td class="p-4 font-bold text-blue-900"><?php echo $role['role_name']; ?></td>
                            <td class="p-4 text-gray-500 text-xs italic"><?php echo $role['description']; ?></td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-1">
                                <?php 
                                    $rid = $role['id'];
                                    $perm_ids = []; 
                                    // Quotes around '$rid' are mandatory for UUID strings
                                    $res = $conn->query("SELECT p.id, p.perm_name FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = '$rid'");
                                    while($p = $res->fetch_assoc()) {
                                        $perm_ids[] = $p['id'];
                                        echo "<span class='bg-blue-50 text-blue-600 px-2 py-0.5 rounded text-[9px] font-bold uppercase border border-blue-100'>".$p['perm_name']."</span>";
                                    }
                                    $perm_json = json_encode($perm_ids);
                                ?>
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-3">
                                    <!-- Wrapped $role['id'] in quotes for JS string handling -->
                                    <button onclick='openRoleModal("edit", "<?php echo $role["id"]; ?>", "<?php echo addslashes($role["role_name"]); ?>", "<?php echo addslashes($role["description"]); ?>", <?php echo $perm_json; ?>)' 
                                            class="text-blue-600 hover:text-blue-900 font-bold text-xs uppercase flex items-center gap-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    
                                    <!-- Using UUID constant for System Admin check -->
                                    <?php if($role['id'] !== $sys_admin_uuid): ?>
                                    <button onclick="confirmDelete('<?php echo $role['id']; ?>', '<?php echo addslashes($role['role_name']); ?>')" 
                                            class="text-red-500 hover:text-red-700 font-bold text-xs uppercase flex items-center gap-1">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
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

    <!-- Modal (Remains largely the same, but IDs are handled as strings) -->
    <div id="roleModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-lg w-full max-w-md shadow-2xl overflow-hidden">
            <form action="process_role.php" method="POST" id="roleForm">
                <input type="hidden" name="action" id="modal_action" value="save">
                <input type="hidden" name="role_id" id="modal_role_id">
                
                <div class="bg-blue-900 p-4 text-white flex justify-between items-center">
                    <h3 class="font-bold text-sm uppercase tracking-wider" id="modal_title">Role Configuration</h3>
                    <button type="button" onclick="closeModal()" class="text-xl hover:text-gray-300">&times;</button>
                </div>
                
                <div class="p-6">
                    <div class="mb-4">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Role Name</label>
                        <input type="text" name="role_name" id="modal_role_name" required 
                               class="w-full border-b p-2 font-bold text-blue-900 outline-none focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Description</label>
                        <input type="text" name="description" id="modal_description" 
                               class="w-full border-b p-2 text-sm text-gray-600 outline-none focus:border-blue-500">
                    </div>

                    <label class="text-[10px] font-bold text-gray-400 uppercase block mb-3">Permissions Access</label>
                    <div class="grid grid-cols-2 gap-3 bg-gray-50 p-4 rounded border border-dashed max-h-48 overflow-y-auto">
                        <?php 
                        $all_permissions_result->data_seek(0);
                        while($p = $all_permissions_result->fetch_assoc()): 
                        ?>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="permissions[]" value="<?php echo $p['id']; ?>" id="p_<?php echo $p['id']; ?>" class="perm-checkbox w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                            <label for="p_<?php echo $p['id']; ?>" class="text-[11px] font-semibold text-gray-700 cursor-pointer select-none"><?php echo $p['perm_name']; ?></label>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 flex justify-end gap-3 border-t">
                    <button type="button" onclick="closeModal()" class="text-xs font-bold text-gray-400 uppercase hover:text-gray-600">Cancel</button>
                    <button type="submit" class="bg-blue-800 text-white px-6 py-2 rounded text-xs font-bold uppercase shadow-lg hover:bg-blue-700 transition">Save Configuration</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRoleModal(mode, id = '', name = '', desc = '', permissions = []) {
            document.getElementById('modal_role_id').value = id;
            document.getElementById('modal_role_name').value = name;
            document.getElementById('modal_description').value = desc;
            
            const checkboxes = document.querySelectorAll('.perm-checkbox');
            checkboxes.forEach(cb => cb.checked = false);

            if (mode === 'add') {
                document.getElementById('modal_title').innerHTML = '<i class="fas fa-plus mr-2"></i> Create New Role';
            } else {
                document.getElementById('modal_title').innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Role Permissions';
                permissions.forEach(pId => {
                    const cb = document.getElementById('p_' + pId);
                    if(cb) cb.checked = true;
                });
            }
            document.getElementById('roleModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('roleModal').classList.add('hidden');
        }

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Delete role: " + name,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // ID is passed as a string here
                    window.location.href = "process_role.php?action=delete&id=" + id;
                }
            })
        }
    </script>
</body>
</html>