<?php
require_once __DIR__ . "/../init.php";
ob_start();

checkAuth();
$isAdmin = RoleHelper::isAdmin($role);
$isUser = RoleHelper::isUser($role);

if (isset($_GET['u'])) {
    $user = $_GET['u'];
} else {
    $user = $user_id;
}

try {
    // Efficiently check if user exists
    $checkSql = "SELECT COUNT(*) as count FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count === 0) {
        setToast("User not found", "error");
        header("Location: ../public/users.php");
        exit;
    }

    // Proceed with loading the user's data
    $sql = "SELECT user.user_id, user.username, user_profile.dept_id, user_profile.email, user_role.role_name, user.user_role_id, 
    user_profile.user_first_name, user_profile.user_middle_name, user_profile.user_last_name, user_profile.user_prof,
    user_profile.user_birthdate, user_status.user_status_name, department.dept_name
FROM user
LEFT JOIN user_role 
    ON user_role.user_role_id = user.user_role_id
LEFT JOIN user_status 
    ON user_status.user_status_id = user.user_status_id
LEFT JOIN user_profile 
    ON user_profile.user_id = user.user_id
LEFT JOIN department
    ON user_profile.dept_id = department.dept_id
    WHERE user.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}
$stmt->close();

$roleOptions = fetchRoles($conn);
$deptOptions = fetchDept($conn);
$queryParams = $_GET;
unset($queryParams['page']); // Remove existing 'page' param if present

// Build base URL with other parameters
$baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
$baseQuery = http_build_query($queryParams);
$separator = $baseQuery ? '&' : '?';
$paginationBase = $baseUrl . ($baseQuery ? '?' . $baseQuery : '') . $separator;


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
</head>
<body>
    <div class="flex gap-4">
        <!-- Profile Section Wrapper -->
        <div
            class="w-full max-w-sm bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-gray-100 overflow-hidden h-fit sticky top-24">

            <!-- Decorative Banner -->
            <div class="relative h-28 bg-gradient-to-r from-cyan-500 to-purple-600">
                <!-- Edit Button (Positioned Absolute Top Right) -->
                <button class="p-2 text-black bg-blue-600 rounded-lg transition-all group" onclick="openEditUserModal(
        '<?= htmlspecialchars($userData['user_id'] ?? '') ?>', 
                        '<?= htmlspecialchars($userData['username'] ?? '') ?>',
                        '<?= htmlspecialchars($userData['user_role_id'] ?? '') ?>',
                        '<?= htmlspecialchars($userData['dept_id'] ?? '') ?>', // Pass ID, not name
                        '<?= htmlspecialchars($userData['email'] ?? '') ?>',
                        '<?= htmlspecialchars($userData['user_first_name'] ?? '') ?>',
                        '<?= htmlspecialchars($userData['user_middle_name'] ?? '') ?>',
                        '<?= htmlspecialchars($userData['user_last_name'] ?? '') ?>',
                        '<?= htmlspecialchars($userData['user_dob'] ?? '') ?>', 
                        '../public/img/prof_pic/<?= htmlspecialchars($userData['user_prof'] ?? 'default.png') ?>'
                    )">
                    EDIT USER
                </button>
            </div>

            <!-- Profile Content -->
            <div class="px-6 pb-6 relative">

                <!-- Profile Image (Negative margin to overlap banner) -->
                <div class="flex justify-center -mt-14 mb-4">
                    <div class="relative">
                        <img src="/public/img/prof_pic/<?= htmlspecialchars($userData['user_prof']) ?>" alt="Profile"
                            class="w-28 h-28 rounded-full object-cover border-[4px] border-white shadow-md bg-white">
                        <!-- Status Indicator (Optional) -->
                        <div class="absolute bottom-2 right-2 w-4 h-4 bg-green-500 border-2 border-white rounded-full">
                        </div>
                    </div>
                </div>

                <!-- Name & Role -->
                <div class="text-center mb-6">
                    <?php
                    $firstName = ucfirst($userData['user_first_name']);
                    $middleInitial = !empty($userData['user_middle_name']) ? strtoupper(substr($userData['user_middle_name'], 0, 1)) . '.' : '';
                    $lastName = ucfirst($userData['user_last_name']);
                    $fullName = htmlspecialchars(trim("$firstName $middleInitial $lastName"));
                    ?>
                    <h2 class="text-xl font-bold text-gray-800 tracking-tight"><?= $fullName ?></h2>
                    <span
                        class="inline-block mt-1 px-3 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 uppercase tracking-wide">
                        <?= htmlspecialchars($userData['role_name']) ?>
                    </span>
                    <span
                        class="inline-block mt-1 px-3 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 uppercase tracking-wide">
                        <?= htmlspecialchars($userData['dept_name']) ?>
                    </span>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100 mb-6"></div>

                <!-- Information List -->
                <div class="space-y-4">

                    <!-- Username -->
                    <div class="flex items-start gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Username
                            </p>
                            <p class="text-sm font-medium text-gray-700">
                                <?= htmlspecialchars($userData['username']) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex items-start gap-4">
                        <div class="overflow-hidden">
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Email
                                Address</p>
                            <p class="text-sm font-medium text-gray-700 truncate"
                                title="<?= htmlspecialchars($userData['email']) ?>">
                                <?= htmlspecialchars($userData['email']) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Birthdate -->
                    <div class="flex items-start gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Birthdate
                            </p>
                            <p class="text-sm font-medium text-gray-700">
                                <?= !empty($userData['user_birthdate']) ? date('F j, Y', strtotime($userData['user_birthdate'])) : "Not set" ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit User Modal -->
    <div id="editUserModal"
        class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm items-center justify-center transition-all duration-300 ease-out font-sans hidden">

        <!-- Modal Content Card -->
        <div id="editUserModalContent"
            class="bg-white w-full max-w-3xl shadow-2xl transform transition-all duration-300 scale-95 opacity-0 flex flex-col max-h-[90vh] rounded-2xl overflow-hidden">

            <!-- 1. Header -->
            <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-white z-10 shrink-0">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">Edit User Profile</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Update personal details and account settings</p>
                </div>
            </div>

            <!-- 2. Scrollable Form Body -->
            <div class="flex-1 overflow-y-auto custom-scroll px-8 pt-8 pb-2">
                <form id="editUserForm" method="POST" action="../controllers/edit_user_profile.php" class="space-y-8"
                    enctype="multipart/form-data">
                    <input type="hidden" name="user_id" id="editUserId">

                    <!-- SECTION: Profile Image & Identity -->
                    <div class="flex flex-col sm:flex-row gap-8">
                        <!-- Image Upload -->
                        <div class="shrink-0 flex flex-col items-center sm:items-start gap-3">
                            <div class="relative group">
                                <div id="editUserPicture"
                                    class="w-32 h-32 rounded-full bg-slate-100 border-4 border-white shadow-md flex items-center justify-center overflow-hidden object-cover">
                                    <span class="text-slate-400 text-xs font-medium">No Image</span>
                                </div>
                                <label for="user_image"
                                    class="absolute bottom-0 right-0 p-2 text-white rounded-full cursor-pointer hover:bg-purple-700 shadow-lg transition-transform hover:scale-105 border-2 border-white">
                                </label>
                                <input type="file" id="user_image" name="user_image" accept="image/*">
                            </div>
                            <p class="text-[11px] text-slate-400 font-medium text-center w-32">Allowed: JPG, PNG</p>
                        </div>

                        <!-- Name Fields -->
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- First Name -->
                            <div class="col-span-2 sm:col-span-1">
                                <label for="editUserFirstName"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">First
                                    Name <span class="text-red-500">*</span></label>
                                <input type="text" name="first_name" id="editUserFirstName"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all"
                                    placeholder="e.g. Juan">
                            </div>
                            <!-- Middle Name -->
                            <div class="col-span-2 sm:col-span-1">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="editUserMiddleName"
                                        class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Middle
                                        Name</label>
                                    <input type="text" name="middle_name" id="editUserMiddleName"
                                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all disabled:opacity-50 disabled:bg-slate-100 disabled:cursor-not-allowed"
                                        placeholder="Middle Name">
                                </div>
                            </div>
                            <!-- Last Name -->
                            <div class="col-span-2 sm:col-span-1">
                                <label for="editUserLastName"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Last
                                    Name <span class="text-red-500">*</span></label>
                                <input type="text" name="last_name" id="editUserLastName"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all"
                                    placeholder="e.g. Dela Cruz">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label for="editUserBirthdate"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Birthday<span class="text-red-500">*</span></label>
                                <input type="date" name="birth_date" id="editUserBirthdate"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION: Account Details (Card Style) -->
                    <div class=" bg-slate-50/80 p-5 rounded-2xl border border-slate-100">
                        <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                            Account Information
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Username -->
                            <div>
                                <label for="editUserName"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Username</label>
                                <input type="text" name="username" id="editUserName"
                                    class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all font-semibold text-slate-700">
                            </div>
                        </div>
                        <!--User Role-->
                        <div>
                            <label for="edit_user_role">User Role</label>
                            <select name="user_role" id="edit_user_role">
                                <option value="" disabled selected>Select a role</option>
                                <?php foreach ($roleOptions as $option): ?>
                                    <option value="<?= htmlspecialchars($option['user_role_id']) ?>">
                                        <?= htmlspecialchars($option['role_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Department -->
                         <div>
                            <label for="edit_user_role">Department</label>
                            <select name="department" id="edit_department">
                                <option value="" disabled selected>Select a department</option>
                                <?php foreach ($deptOptions as $option): ?>
                                    <option value="<?= htmlspecialchars($option['dept_id']) ?>">
                                        <?= htmlspecialchars($option['dept_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Email -->
                        <div class="col-span-1 sm:col-span-2">
                            <label for="editUserEmail"
                                class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Email
                                Address</label>
                            <input type="email" name="email" id="editUserEmail"
                                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all">
                        </div>
                    </div>

                </form>
            </div>

            <!-- 3. Sticky Footer Action Buttons -->
            <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 z-10 shrink-0">
                <button type="button" onclick="closeEditUserModal()"
                    class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-semibold rounded-xl text-sm hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
                    Cancel
                </button>
                <button type="submit" form="editUserForm"
                    class="px-6 py-2.5 bg-ntPurple text-black font-semibold rounded-xl text-sm hover:bg-purple-700 shadow-md shadow-purple-200 transition-all transform hover:-translate-y-0.5">
                    Save Changes
                </button>
            </div>

        </div>
    </div>
    <?php ob_end_flush(); ?>
</body>
<script src="js/user_profile.js"></script>
</html>