<?php
session_start();
require_once __DIR__ . "/../init.php";
$user = checkAuth('Admin');

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
    <link rel="stylesheet" href="./output.css">
    <title>Add User</title>
</head>

<body>

<div
        class="bg-white border border-gray-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] rounded-2xl overflow-hidden flex flex-col">

        <!-- Main User List -->
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50/50 sticky top-0 z-10 backdrop-blur-sm">
                    <tr class="border-b border-gray-100">
                        <!-- Headers -->
                        <th class="p-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-left">
                            Full Name</th>
                        <th class="p-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">
                            Username</th>
                        <th class="p-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">
                            Email</th>
                        <th class="p-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">
                            Department</th>
                        <th class="p-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">
                            Role</th>
                        <th class="p-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center w-32">
                            Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody" class="divide-y divide-gray-50">
                    <?php
                    // Initial Load (Server Side Rendering)
                    if (!empty($users)):
                        foreach ($users as $user):
                            // Re-use logic for consistency
                            $user_id = $user['user_id'];
                            $middleInitial = '';
                            $firstName = !empty($user['user_first_name']) ? ucfirst($user['user_first_name']) : '';
                            if (!empty($user['user_middle_name'])) {
                                $middleInitial = strtoupper(substr($user['user_middle_name'], 0, 1)) . '.';
                            }
                            $lastName = !empty($user['user_last_name']) ? ucfirst($user['user_last_name']) : '';
                            $fullName = htmlspecialchars(trim("$firstName $middleInitial $lastName"));
                            $uploadDir = 'img/prof_pic/';

                            // 2. Check if the image exists in the database. 
// NOTE: Use 'user_prof' if that's the column name in your user_profile table.
                            $dbImage = !empty($user['user_prof']) ? $user['user_prof'] : '';

                            // 3. Construct the full path
                            if (!empty($dbImage) && $dbImage !== 'default.png') {
                                $userImage = $uploadDir . $dbImage;
                            } else {
                                $userImage = $uploadDir . 'default.png';
                            }

                            // 4. Set a flag for the "Initial" fallback
                            $isDefault = (strpos($userImage, 'default.png') !== false);
                            // Assume $current_session_user_id is available from yourauth middleware
                            $isSelf = (isset($_SESSION['user_id']) && $user['user_id'] == $_SESSION['user_id']);
                            ?>
                            <tr>
                                <!-- Full Name + Avatar -->
                                <td class="p-4 whitespace-nowrap">
                                    <a href="../public/user_profile.php?u=<?= htmlspecialchars($user['user_id']) ?>"
                                        class="flex items-center gap-3 group">

                                        <div
                                            class="h-8 w-8 rounded-full border border-white shadow-sm flex items-center justify-center text-xs font-bold 
                                            <?= $isDefault ? 'bg-gradient-to-br from-cyan-100 to-blue-100 text-cyan-700' : '' ?>">

                                            <?php if ($isDefault): ?>
                                                <?= strtoupper(substr($firstName, 0, 1)); ?>
                                            <?php else: ?>
                                                <img src="<?= htmlspecialchars($userImage) ?>" alt="Profile"
                                                    class="h-full w-full rounded-full object-cover">
                                            <?php endif; ?>
                                        </div>

                                        <span
                                            class="text-sm font-bold text-gray-700 group-hover:text-purple-700 transition-colors">
                                            <?= $fullName ?>
                                        </span>
                                    </a>
                                </td>
                                <td class="p-4 text-center"><span
                                        class="px-2 py-1 bg-gray-50 text-gray-600 rounded text-xs font-mono"><?= htmlspecialchars($user['username']) ?></span>
                                </td>
                                <td class="p-4 text-center text-sm text-gray-600">
                                    <?= htmlspecialchars($user['email']) ?>
                                </td>
                                <td class="p-4 text-center text-sm text-gray-600">
                                    <?= htmlspecialchars($user['dept_name']) ?>
                                </td>
                                <td class="p-4 text-center"><span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100"><?= htmlspecialchars($user['role_name']) ?></span>
                                </td>

                                <!-- Actions -->
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-4">
                                        <button
                                            onclick="openEditUserModal('<?= $user['user_id'] ?>', '<?= addslashes($user['username']) ?>', '<?= $user['user_role_id'] ?>','<?= addslashes($user['password'] ?? '') ?>')"
                                            class="text-xs font-bold uppercase tracking-wider text-cyan-600 hover:text-cyan-800 transition-colors">
                                            Edit
                                        </button>

                                        <?php if (!$isSelf): ?>
                                            <button onclick="openArchiveUserModal('<?= $user['user_id'] ?>')"
                                                class="text-xs font-bold uppercase tracking-wider text-red-500 hover:text-red-700 transition-colors">
                                                Archive
                                            </button>
                                        <?php else: ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-12 w-12 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6 text-gray-300">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm">No users found.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <form method="POST" action="../controllers/add_user.php" autocomplete="off" enctype="multipart/form-data">
        <div>
            <label for="user_prof_pic">Upload Profile Picture</label>
            <input type="file" name="user_prof_pic" id="prof_pic">
        </div>
        <div>
            <label for="username">Username <span class="text-red-500">*</span>
            </label>
            <input type="text" name="username" id="username"
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all"
                placeholder="Enter username" data-required="true" data-error="Username is required"
                data-check-url="../helpers/check_availability.php?field=username&table=user&value="
                data-check-message="Username already exists." />
            <p class="error-message text-red-500 text-xs mt-1 hidden font-medium"></p>
        </div>
        <div>
            <label for="email">
                Email Address <span class="text-red-500">*</span>
            </label>
            <input type="text" name="email" id="email"
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all"
                placeholder="Enter email" data-required="true" data-error="Email is required"
                data-check-url="../helpers/check_availability.php?field=username&table=user&value="
                data-check-message="Email already exists.">
            <p class="error-message text-red-500 text-xs mt-1 hidden font-medium"></p>
        </div>
        <div>
            <label for="user_roles" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                System Role <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <select name="user_role" id="user_role">
                    <option value="" disabled selected>Select a role</option>
                    <?php foreach ($roleOptions as $option): ?>
                        <option value="<?= htmlspecialchars($option['user_role_id']) ?>">
                            <?= htmlspecialchars($option['role_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
            <p class="error-message text-red-500 text-xs mt-1 hidden font-medium"></p>
        </div>
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                Password <span class="text-red-500">*</span>
            </label>
            <input type="password" name="password" id="password"
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all"
                placeholder="••••••••" data-required="true" data-error="User password is required" />
            <p class="error-message text-red-500 text-xs mt-1 hidden font-medium"></p>
        </div>

        <div>
            <label for="department">Department:</label>
            <select name="department" id="department">
                <option value="" disabled selected>Select a Department</option>
                <?php foreach ($deptOptions as $option): ?>
                    <option value="<?= htmlspecialchars($option['dept_id']) ?>">
                        <?= htmlspecialchars($option['dept_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


        <!-- SECTION: Personal Information -->
        <div>
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                Personal Profile
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- First Name -->
                <div>
                    <label for="firstName"
                        class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name" id="firstName"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all"
                        placeholder="Given Name" data-required="true" data-error="First name is required" />
                    <p class="error-message text-red-500 text-xs mt-1 hidden font-medium"></p>
                </div>
                <div>
                    <label for="firstName"
                        class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                        Middle Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="middle_name" id="middleName"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all"
                        placeholder="Middle Name" data-required="true" data-error="middle name is required" />
                    <p class="error-message text-red-500 text-xs mt-1 hidden font-medium"></p>
                </div>

                <!-- Last Name -->
                <div>
                    <label for="lastName"
                        class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="last_name" id="lastName"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all"
                        placeholder="Family Name" data-required="true" data-error="Last name is required" />
                    <p class="error-message text-red-500 text-xs mt-1 hidden font-medium"></p>
                </div>

                <div>
                    <label for="birthday" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Birthday
                    </label>
                    <div class="flex items-center gap-1.5">
                        <input type="date" id="birthday" name="birthday">
                    </div>
                </div>

                <div>
                    <button type="submit"> Add User</button>
                </div>
    </form>

    <!-- Edit Modal -->
     <h1>Edit user</h1>
    <div id="editUserModal" class="hidden">
        <div id="editUserModalContent">
            
            <form action="../controllers/edit_user.php" method="POST">
                <div><!-- Modal content goes here -->
                    <!-- Hidden field to store user ID for editing -->
                    <input type="hidden" name="user_id" id="editUserId">
                    <!-- Username-->
                    <label for="edit_username">Username</label>
                    <input type="text" id="edit_username" name="edit_username" placeholder="Username">
                    <!--User Roles-->
                    <label for="edit_user_role">User Role</label>
                    <select name="edit_user_role" id="edit_user_role">
                        <option value="" disabled selected>Select a role</option>
                        <?php foreach ($roleOptions as $option): ?>
                            <option value="<?= htmlspecialchars($option['user_role_id']) ?>">
                                <?= htmlspecialchars($option['role_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!--password-->
                    <label for="password">Password</label>
                    <input type="password" name="edit_password" id="edit_password"
                        placeholder="Leave Blank to keep current">
                    <button type="submit" class="bg-blue-500">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</body>
    <script src="js/user_list.js"></script>
</html>