<?php
require_once __DIR__ . "/../init.php";

// Renamed to $userData to avoid conflict with the foreach ($users as $user) loop below
$userData = checkAuth('Admin');

$userVisibility = new UserVisibility($conn);
$users = $userVisibility->getVisibleUsers(20, 0);
$roleOptions = fetchRoles($conn);
$deptOptions = fetchDept($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="dist/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>User Management | PRC DTS</title>
</head>

<body class="bg-slate-50 flex min-h-screen font-sans relative">

    <!-- SIDEBAR -->
    <aside class="w-72 bg-white border-r border-slate-200 flex flex-col hidden md:flex">
        <div class="p-8 border-b border-slate-50 text-center">
            <img src="img/prclogo.png" class="h-16 w-16 mx-auto mb-4" style="mix-blend-mode: multiply;">
            <p class="text-blue-900 font-black text-sm uppercase tracking-tighter">PRC Administration</p>
        </div>

        <nav class="flex-1 p-6 space-y-3">
            <a href="admin_dashboard.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-chart-line w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Overview</span>
            </a>
            <a href="documents.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-file w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Documents</span>
            </a>
            <a href="tracking.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-search w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Document Tracking</span>
            </a>
        
            <a href="receive.php" class="flex items-center p-3 text-slate-500 hover:bg-slate-50 rounded-xl transition-all group">
                <i class="fas fa-file-import w-6 text-center mr-3 group-hover:text-blue-900"></i>
                <span>Receive Document</span>
            </a>
            <!-- Active State on User Management -->
            <a href="users.php" class="flex items-center p-3 bg-blue-50 text-blue-900 rounded-xl font-bold shadow-sm mt-6">
                <i class="fas fa-users-cog w-6 text-center mr-3"></i>
                <span>User Management</span>
            </a>
        </nav>

        <div class="p-6 border-t border-slate-50">
            <a href="../controllers/logout_handler.php" class="flex items-center p-3 text-red-500 hover:bg-red-50 rounded-xl font-bold transition-all">
                <i class="fas fa-right-from-bracket w-6 text-center mr-3"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-screen overflow-y-auto relative">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200 h-20 flex-shrink-0 flex items-center justify-between px-10">
            <div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest">System Administration</h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-800 leading-none"><?= htmlspecialchars($userData->full_name ?? 'Unknown User') ?></p>
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-tighter mt-1"><?= htmlspecialchars($userData->role ?? 'N/A') ?></p>
                </div>
                <div class="h-10 w-10 bg-slate-100 rounded-full flex items-center justify-center border border-slate-200">
                    <i class="fas fa-user text-slate-400"></i>
                </div>
            </div>
        </header>

        <div class="p-10">
            
            <!-- Page Header with Button -->
            <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">User Management</h1>
                    <p class="text-slate-500 mt-1">Manage system accounts, roles, and administrative access.</p>
                </div>
                <button onclick="openAddUserModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-md hover:shadow-lg text-sm flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>
            </div>

            <!-- Users Table Card -->
            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-10">
                <div class="bg-slate-50 p-4 border-b border-slate-100 px-8 flex justify-between items-center">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">System Users</span>
                </div>
                
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-white border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-left">Full Name</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Email</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Department</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Role</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody" class="text-sm">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user):
                                    $user_id = $user['user_id'];
                                    $middleInitial = '';
                                    $firstName = !empty($user['user_first_name']) ? ucfirst($user['user_first_name']) : '';
                                    if (!empty($user['user_middle_name'])) {
                                        $middleInitial = strtoupper(substr($user['user_middle_name'], 0, 1)) . '.';
                                    }
                                    $lastName = !empty($user['user_last_name']) ? ucfirst($user['user_last_name']) : '';
                                    $fullName = htmlspecialchars(trim("$firstName $middleInitial $lastName"));
                                    
                                    $uploadDir = 'img/prof_pic/';
                                    $dbImage = !empty($user['user_prof']) ? $user['user_prof'] : '';

                                    if (!empty($dbImage) && $dbImage !== 'default.png') {
                                        $userImage = $uploadDir . $dbImage;
                                    } else {
                                        $userImage = $uploadDir . 'default.png';
                                    }

                                    $isDefault = (strpos($userImage, 'default.png') !== false);
                                    $isSelf = (isset($_SESSION['user_id']) && $user['user_id'] == $_SESSION['user_id']);
                                ?>
                                <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                    
                                    <!-- Full Name + Avatar -->
                                    <td class="px-8 py-4 whitespace-nowrap">
                                        <a href="../public/user_profile.php?u=<?= htmlspecialchars($user['user_id']) ?>" class="flex items-center gap-3 group">
                                            <div class="h-9 w-9 rounded-full border border-slate-200 shadow-sm flex items-center justify-center text-xs font-bold overflow-hidden bg-slate-100 text-slate-500 <?= $isDefault ? 'bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-700' : '' ?>">
                                                <?php if ($isDefault): ?>
                                                    <?= strtoupper(substr($firstName, 0, 1)); ?>
                                                <?php else: ?>
                                                    <img src="<?= htmlspecialchars($userImage) ?>" alt="Profile" class="h-full w-full object-cover">
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-sm font-bold text-slate-700 group-hover:text-blue-600 transition-colors">
                                                <?= $fullName ?>
                                            </span>
                                        </a>
                                    </td>

                                    <!-- Details -->
                                    <td class="px-8 py-4 text-center text-slate-600 font-medium">
                                        <?= htmlspecialchars($user['email']) ?>
                                    </td>
                                    <td class="px-8 py-4 text-center text-slate-600">
                                        <?= htmlspecialchars($user['dept_name']) ?>
                                    </td>
                                    <td class="px-8 py-4 text-center">
                                        <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                            <?= htmlspecialchars($user['role_name']) ?>
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-8 py-4 text-center">
                                        <div class="flex items-center justify-center gap-4">
                                            <button onclick="openEditUserModal('<?= $user['user_id'] ?>', '<?= addslashes($user['username']) ?>', '<?= $user['user_role_id'] ?>', '<?= addslashes($user['password'] ?? '') ?>')"
                                                class="text-[10px] font-black uppercase tracking-wider text-blue-500 hover:text-blue-700 transition-colors flex items-center gap-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>

                                            <?php if (!$isSelf): ?>
                                                <button onclick="openArchiveUserModal('<?= $user['user_id'] ?>')"
                                                    class="text-[10px] font-black uppercase tracking-wider text-red-500 hover:text-red-700 transition-colors flex items-center gap-1">
                                                    <i class="fas fa-archive"></i> Archive
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-8 py-16 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                                <i class="fas fa-users-slash text-2xl text-slate-300"></i>
                                            </div>
                                            <span class="text-sm italic">No users found.</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- ========================================== -->
    <!-- ADD USER MODAL                             -->
    <!-- ========================================== -->
    <div id="addUserModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeAddUserModal()"></div>
        
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-4xl relative z-10 overflow-hidden flex flex-col transform transition-all max-h-[90vh]">
            <div class="bg-slate-50 p-5 border-b border-slate-100 flex justify-between items-center px-8">
                <span class="text-xs font-black text-slate-800 uppercase tracking-widest">Register New User</span>
                <button onclick="closeAddUserModal()" class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <div class="p-8 overflow-y-auto">
                <form method="POST" action="../controllers/add_user.php" autocomplete="off" enctype="multipart/form-data">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <!-- Account Details -->
                        <div class="space-y-5">
                            <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">Account Details</h3>
                            
                            <div>
                                <label for="prof_pic" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Profile Picture</label>
                                <input type="file" name="user_prof_pic" id="prof_pic" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer">
                            </div>

                            <div>
                                <label for="username" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Username <span class="text-red-500">*</span></label>
                                <input type="text" name="username" id="username" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all" placeholder="Enter username" data-required="true" data-error="Username is required" data-check-url="../helpers/check_availability.php?field=username&table=user&value=" data-check-message="Username already exists." />
                                <p class="error-message text-red-500 text-[10px] uppercase font-bold mt-1 hidden"></p>
                            </div>

                            <div>
                                <label for="email" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="email" id="email" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all" placeholder="Enter email" data-required="true" data-error="Email is required" data-check-url="../helpers/check_availability.php?field=email&table=user&value=" data-check-message="Email already exists.">
                                <p class="error-message text-red-500 text-[10px] uppercase font-bold mt-1 hidden"></p>
                            </div>

                            <div>
                                <label for="password" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Password <span class="text-red-500">*</span></label>
                                <input type="password" name="password" id="password" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all" placeholder="••••••••" data-required="true" data-error="User password is required" />
                                <p class="error-message text-red-500 text-[10px] uppercase font-bold mt-1 hidden"></p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="user_role" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">System Role <span class="text-red-500">*</span></label>
                                    <select name="user_role" id="user_role" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all cursor-pointer">
                                        <option value="" disabled selected>Select Role</option>
                                        <?php foreach ($roleOptions as $option): ?>
                                            <option value="<?= htmlspecialchars($option['user_role_id']) ?>"><?= htmlspecialchars($option['role_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="department" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Department</label>
                                    <select name="department" id="department" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all cursor-pointer">
                                        <option value="" disabled selected>Select Dept</option>
                                        <?php foreach ($deptOptions as $option): ?>
                                            <option value="<?= htmlspecialchars($option['dept_id']) ?>"><?= htmlspecialchars($option['dept_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Profile -->
                        <div class="space-y-5">
                            <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">Personal Profile</h3>
                            
                            <div>
                                <label for="firstName" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">First Name <span class="text-red-500">*</span></label>
                                <input type="text" name="first_name" id="firstName" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all" placeholder="Given Name" data-required="true" data-error="First name is required" />
                                <p class="error-message text-red-500 text-[10px] uppercase font-bold mt-1 hidden"></p>
                            </div>

                            <div>
                                <label for="middleName" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Middle Name <span class="text-red-500">*</span></label>
                                <input type="text" name="middle_name" id="middleName" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all" placeholder="Middle Name" data-required="true" data-error="Middle name is required" />
                                <p class="error-message text-red-500 text-[10px] uppercase font-bold mt-1 hidden"></p>
                            </div>

                            <div>
                                <label for="lastName" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Last Name <span class="text-red-500">*</span></label>
                                <input type="text" name="last_name" id="lastName" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all" placeholder="Family Name" data-required="true" data-error="Last name is required" />
                                <p class="error-message text-red-500 text-[10px] uppercase font-bold mt-1 hidden"></p>
                            </div>

                            <div>
                                <label for="birthday" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Birthday</label>
                                <input type="date" id="birthday" name="birthday" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <button type="button" onclick="closeAddUserModal()" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold py-3 px-8 rounded-xl transition-all text-sm">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-sm text-sm flex items-center gap-2">
                            <i class="fas fa-save"></i> Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- EDIT USER MODAL                            -->
    <!-- ========================================== -->
    <div id="editUserModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeEditUserModal()"></div>
        
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md relative z-10 overflow-hidden flex flex-col transform transition-all">
            <div class="bg-slate-50 p-5 border-b border-slate-100 flex justify-between items-center px-8">
                <span class="text-xs font-black text-slate-800 uppercase tracking-widest">Edit User Credentials</span>
                <button onclick="closeEditUserModal()" class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <div class="p-8">
                <form action="../controllers/edit_user.php" method="POST">
                    <input type="hidden" name="user_id" id="editUserId">
                    
                    <div class="mb-5">
                        <label for="edit_username" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Username</label>
                        <input type="text" id="edit_username" name="edit_username" placeholder="Username" required 
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all">
                    </div>
                    
                    <div class="mb-5">
                        <label for="edit_user_role" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">User Role</label>
                        <select name="edit_user_role" id="edit_user_role" required 
                                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all cursor-pointer">
                            <option value="" disabled selected>Select a role</option>
                            <?php foreach ($roleOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option['user_role_id']) ?>"><?= htmlspecialchars($option['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-8">
                        <label for="edit_password" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Password</label>
                        <input type="password" name="edit_password" id="edit_password" placeholder="Leave blank to keep current" 
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none transition-all">
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-50">
                        <button type="button" onclick="closeEditUserModal()" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold py-2.5 px-6 rounded-xl transition-all text-sm">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm text-sm flex items-center gap-2">
                            <i class="fas fa-check"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- PROFILE MODAL -->
<div id="profileModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeProfileModal()"></div>

    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 relative z-10 overflow-hidden">

        <div class="px-8 py-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
            <span class="text-xs font-black uppercase tracking-widest text-slate-700">
                User Profile
            </span>

            <button onclick="closeProfileModal()" class="text-slate-400 hover:text-red-500">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="p-8">

            <div class="flex flex-col items-center text-center mb-8">

                <img id="profileImage"
                     src=""
                     class="h-28 w-28 rounded-full object-cover border-4 border-blue-100 shadow-md mb-4">

                <h2 id="profileName" class="text-xl font-black text-slate-800"></h2>

                <p id="profileRole" class="text-xs uppercase font-bold text-blue-600 tracking-widest mt-1"></p>

            </div>

            <div class="space-y-4">

                <div class="bg-slate-50 rounded-2xl p-4">
                    <p class="text-[10px] uppercase font-black text-slate-400">Email</p>
                    <p id="profileEmail" class="font-bold text-slate-700 mt-1"></p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-4">
                    <p class="text-[10px] uppercase font-black text-slate-400">Department</p>
                    <p id="profileDept" class="font-bold text-slate-700 mt-1"></p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-4">
                    <p class="text-[10px] uppercase font-black text-slate-400">Role</p>
                    <p id="profileRole2" class="font-bold text-slate-700 mt-1"></p>
                </div>

            </div>

        </div>

    </div>
</div>

    <!-- Scripts -->
     <script>
const profileModal = document.getElementById('profileModal');

function openProfileModal(id, name, email, dept, role, image) {

    document.getElementById('profileName').textContent = name;
    document.getElementById('profileEmail').textContent = email;
    document.getElementById('profileDept').textContent = dept;
    document.getElementById('profileRole').textContent = role;
    document.getElementById('profileRole2').textContent = role;
    document.getElementById('profileImage').src = image;

    profileModal.classList.remove('hidden');
}

function closeProfileModal() {
    profileModal.classList.add('hidden');
}

document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
        closeProfileModal();
    }
});
</script>
    <script>
        // ADD USER MODAL
        const addModal = document.getElementById('addUserModal');
        function openAddUserModal() {
            addModal.classList.remove('hidden');
        }
        function closeAddUserModal() {
            addModal.classList.add('hidden');
        }

        // EDIT USER MODAL
        const editModal = document.getElementById('editUserModal');
        function openEditUserModal(id, username, roleId, passwordStr) {
            // Populate form fields
            document.getElementById('editUserId').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_user_role').value = roleId;
            document.getElementById('edit_password').value = ''; // Don't prefill password
            
            // Show modal
            editModal.classList.remove('hidden');
        }
        function closeEditUserModal() {
            editModal.classList.add('hidden');
        }

        // Close modals on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                if (!addModal.classList.contains('hidden')) closeAddUserModal();
                if (!editModal.classList.contains('hidden')) closeEditUserModal();
            }
        });
    </script>
    
    <!-- Your custom user list script -->
    <script src="js/user_list.js"></script>
    
</body>
</html>