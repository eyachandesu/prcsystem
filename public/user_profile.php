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
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - <?= htmlspecialchars($userData['username'] ?? 'User') ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full text-gray-800 antialiased">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Breadcrumb / Back Button -->
        <div class="mb-6">
            <a href="../public/users.php" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Users
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: Profile Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                    <!-- Avatar -->
                    <div class="relative w-32 h-32 mx-auto mb-4">
                        <?php if (!empty($userData['user_prof'])): ?>
                            <img class="w-full h-full rounded-full object-cover border-4 border-indigo-50" src="<?= htmlspecialchars($userData['user_prof']) ?>" alt="Avatar">
                        <?php else: ?>
                            <div class="w-full h-full rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 text-3xl font-bold border-4 border-indigo-50">
                                <?= strtoupper(substr($userData['user_first_name'] ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <!-- Status Badge -->
                        <span class="absolute bottom-1 right-1 block h-5 w-5 rounded-full border-2 border-white 
                            <?= ($userData['user_status_name'] === 'Active') ? 'bg-green-400' : 'bg-gray-400' ?>"></span>
                    </div>

                    <!-- User Info -->
                    <h2 class="text-xl font-bold text-gray-900">
                        <?= htmlspecialchars(($userData['user_first_name'] ?? '') . ' ' . ($userData['user_last_name'] ?? '')) ?>
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">@<?= htmlspecialchars($userData['username'] ?? '') ?></p>

                    <div class="flex flex-wrap justify-center gap-2 mb-6">
                        <!-- Role Badge -->
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700">
                            <?= htmlspecialchars($userData['role_name'] ?? 'No Role') ?>
                        </span>
                        <!-- Dept Badge -->
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700">
                            <?= htmlspecialchars($userData['dept_name'] ?? 'No Department') ?>
                        </span>
                    </div>

                    <!-- Read-Only Quick Details -->
                    <div class="text-left text-sm border-t border-gray-100 pt-4 space-y-3">
                        <div>
                            <span class="text-gray-500 block font-medium">Email Address</span>
                            <span class="text-gray-900 break-words"><?= htmlspecialchars($userData['email'] ?? 'Not set') ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500 block font-medium">Birthdate</span>
                            <span class="text-gray-900"><?= !empty($userData['user_birthdate']) ? date("F d, Y", strtotime($userData['user_birthdate'])) : 'Not set' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Edit Profile Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 pb-2 border-b border-gray-100">Update Profile Details</h3>
                    
                    <form action="update_user.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($userData['user_id']) ?>">

                        <!-- Name Fields -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-1 block">First Name</label>
                                <input type="text" name="first_name" value="<?= htmlspecialchars($userData['user_first_name'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-1 block">Middle Name</label>
                                <input type="text" name="middle_name" value="<?= htmlspecialchars($userData['user_middle_name'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-1 block">Last Name</label>
                                <input type="text" name="last_name" value="<?= htmlspecialchars($userData['user_last_name'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            </div>
                        </div>

                        <!-- Email & Birthdate -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-1 block">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-1 block">Birthdate</label>
                                <input type="date" name="birthdate" value="<?= htmlspecialchars($userData['user_birthdate'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            </div>
                        </div>

                        <!-- Department & Role (Uses your fetched arrays) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-1 block">Department</label>
                                <select name="dept_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    <option value="">Select Department</option>
                                    <?php foreach ($deptOptions as $dept): ?>
                                        <option value="<?= $dept['dept_id'] ?>" <?= ($dept['dept_id'] == $userData['dept_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['dept_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-1 block">Role</label>
                                <select name="user_role_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" <?= !$isAdmin ? 'disabled' : '' ?>>
                                    <option value="">Select Role</option>
                                    <?php foreach ($roleOptions as $roleOpt): ?>
                                        <option value="<?= $roleOpt['user_role_id'] ?>" <?= ($roleOpt['user_role_id'] == $userData['user_role_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($roleOpt['role_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1 block">Update Profile Picture</label>
                            <input type="file" name="user_prof" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        </div>

                        <!-- Save Button -->
                        <div class="flex justify-end pt-4 border-t border-gray-100">
                            <button type="submit" class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

