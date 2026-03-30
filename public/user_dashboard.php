<?php
session_start();
require_once __DIR__ . "/../config/config.php"; // Assuming init.php or config.php
require_once __DIR__ . "/../helper/generalValidationMessage.php";

// Placeholder for your auth check - Ensure this doesn't overwrite $user in the loop
// $currentUser = checkAuth('Admin'); 

// Fetching Data
// $userVisibility = new UserVisibility($conn);
// $users = $userVisibility->getVisibleUsers(20, 0);
// $roleOptions = fetchRoles($conn);
// $deptOptions = fetchDept($conn);

// MOCK DATA for demonstration (Remove these once your includes work)
$users = $conn->query("SELECT u.*, up.*, ur.role_name FROM user u JOIN user_profile up ON u.user_id = up.user_id JOIN user_role ur ON u.user_role_id = ur.user_role_id")->fetch_all(MYSQLI_ASSOC);
$roleOptions = $conn->query("SELECT * FROM user_role")->fetch_all(MYSQLI_ASSOC);
$deptOptions = $conn->query("SELECT * FROM department")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dist/output.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>User Management | PRC DTS</title>
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>

<body class="bg-slate-50 min-h-screen p-8 font-sans">

    <div class="max-w-7xl mx-auto space-y-10">
        
        <!-- HEADER -->
<div class="flex justify-between items-center mb-10"> <!-- Changed items-end to items-center -->
    <div>
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">User Management</h2>
        <p class="text-slate-400 text-sm font-medium">Create and manage system personnel accounts.</p>
    </div>
    
    <div class="flex items-center gap-4">
        <!-- STATUS BADGE -->
        <div class="bg-white p-2 px-4 rounded-full shadow-sm border border-slate-200 flex items-center gap-2">
            <div class="h-2 w-2 rounded-full bg-green-500"></div>
            <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Admin Control Active</span>
        </div>

        <!-- LOGOUT BUTTON -->
        <a href="../controllers/logout_handler.php" 
           class="bg-white border border-slate-200 text-slate-400 hover:text-red-600 hover:border-red-100 hover:bg-red-50 p-2 px-4 rounded-full transition-all flex items-center gap-2 group shadow-sm">
            <i class="fas fa-power-off text-xs transition-transform group-hover:scale-110"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">Logout</span>
        </a>
    </div>
</div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- LEFT: ADD USER FORM -->
            <div class="lg:col-span-1">
                <form method="POST" action="../controllers/add_user.php" autocomplete="off" enctype="multipart/form-data" 
                      class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden sticky top-8">
                    <div class="bg-slate-900 p-4 px-8 flex justify-between items-center">
                        <span class="text-[10px] font-black text-white uppercase tracking-widest">New Registration</span>
                        <i class="fas fa-user-plus text-white/50 text-xs"></i>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <!-- Profile Pic -->
                        <div class="flex flex-col items-center pb-4">
                            <label class="cursor-pointer group relative">
                                <div class="h-20 w-20 rounded-2xl bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden transition-all group-hover:border-blue-500">
                                    <i class="fas fa-camera text-slate-400 group-hover:text-blue-500"></i>
                                </div>
                                <input type="file" name="user_prof_pic" class="hidden">
                            </label>
                            <p class="text-[9px] font-black text-slate-400 uppercase mt-2">Upload Photo</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Username*</label>
                                <input type="text" name="username" required class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold">
                            </div>
                            <div class="col-span-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Email Address*</label>
                                <input type="email" name="email" required class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">First Name</label>
                                <input type="text" name="first_name" required class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Last Name</label>
                                <input type="text" name="last_name" required class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold">
                            </div>
                            <div class="col-span-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">System Role</label>
                                <select name="user_role" class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold bg-white">
                                    <?php foreach ($roleOptions as $role): ?>
                                        <option value="<?= $role['user_role_id'] ?>"><?= $role['role_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Department</label>
                                <select name="department" class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold bg-white">
                                    <?php foreach ($deptOptions as $dept): ?>
                                        <option value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Password*</label>
                                <input type="password" name="password" required class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold" placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-blue-900 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg hover:bg-blue-800 transition-all">
                            Register User
                        </button>
                    </div>
                </form>
            </div>

            <!-- RIGHT: USER LIST -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                    <div class="p-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Personnel Directory</span>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-xs"></i>
                            <input type="text" placeholder="Search..." class="pl-8 pr-4 py-1.5 rounded-full border border-slate-200 text-xs outline-none focus:border-blue-900">
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                                    <th class="p-6">User / Full Name</th>
                                    <th class="p-6">Role</th>
                                    <th class="p-6">Department</th>
                                    <th class="p-6 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if (!empty($users)): foreach ($users as $u): 
                                    $firstName = ucfirst($u['user_first_name'] ?? '');
                                    $lastName = ucfirst($u['user_last_name'] ?? '');
                                    $fullName = "$firstName $lastName";
                                    $isDefault = empty($u['user_prof']) || $u['user_prof'] == 'default.png';
                                    $userImage = $isDefault ? '' : 'img/prof_pic/' . $u['user_prof'];
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-6">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl overflow-hidden bg-blue-100 flex items-center justify-center text-blue-700 font-black text-xs border border-blue-200">
                                                <?php if($isDefault): ?>
                                                    <?= substr($firstName, 0, 1) ?>
                                                <?php else: ?>
                                                    <img src="<?= $userImage ?>" class="h-full w-full object-cover">
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-800 leading-tight"><?= $fullName ?></p>
                                                <p class="text-[10px] font-bold text-slate-400"><?= $u['username'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-6">
                                        <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-full text-[9px] font-black uppercase border border-purple-100">
                                            <?= $u['role_name'] ?>
                                        </span>
                                    </td>
                                    <td class="p-6 text-xs font-bold text-slate-500">
                                        <?= $u['dept_name'] ?? 'Unassigned' ?>
                                    </td>
                                    <td class="p-6 text-center">
                                        <div class="flex justify-center gap-3">
                                            <button onclick="openEditUserModal('<?= $u['user_id'] ?>')" class="h-8 w-8 rounded-lg bg-slate-50 text-slate-400 hover:text-blue-900 hover:bg-blue-50 transition-all">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button onclick="openArchiveUserModal('<?= $u['user_id'] ?>')" class="h-8 w-8 rounded-lg bg-slate-50 text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all">
                                                <i class="fas fa-archive text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="4" class="p-20 text-center text-slate-300 font-bold italic">No personnel found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL OVERLAY -->
    <div id="editUserModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Update User Details</h3>
                <button onclick="document.getElementById('editUserModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500"><i class="fas fa-times"></i></button>
            </div>
            <form action="../controllers/edit_user.php" method="POST" class="p-8 space-y-6">
                <input type="hidden" name="user_id" id="editUserId">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Username</label>
                    <input type="text" id="edit_username" name="edit_username" class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Update Role</label>
                    <select name="edit_user_role" id="edit_user_role" class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold bg-white">
                        <?php foreach ($roleOptions as $role): ?>
                            <option value="<?= $role['user_role_id'] ?>"><?= $role['role_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">New Password (Leave blank to keep)</label>
                    <input type="password" name="edit_password" class="w-full border-b-2 border-slate-100 p-2 outline-none focus:border-blue-900 text-sm font-bold">
                </div>
                <button type="submit" class="w-full bg-blue-900 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg">Save Changes</button>
            </form>
        </div>
    </div>

    <script src="js/user_list.js"></script>
    <script>
        function openEditUserModal(id) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editUserModal').classList.remove('hidden');
        }
    </script>
</body>
</html>