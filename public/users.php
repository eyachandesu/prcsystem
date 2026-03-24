<?php
ob_start();
//Requires
require_once __DIR__ . "/../init.php";
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
    <form action="POST" action="../controllers/add_user.php" autocomplete="off">
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
            <label for="roles" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                System Role <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <select name="roles" id="roles"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-ntPurple focus:ring-1 focus:ring-ntPurple transition-all appearance-none cursor-pointer"
                    data-required="true" data-error="User role is required">
                    <option value="" disabled selected>Select a Role</option>
                    <?php foreach ($roleOptions as $role): ?>
                        <?php if ($user_role['user_role_id'] >= $role_id): ?>
                            <option value="<?= htmlspecialchars($user_role['user_role_id']) ?>">
                                <?= htmlspecialchars($user_role['role_name']) ?>
                            </option>
                        <?php endif; ?>
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
        <!-- Placeholder muna -->
        <div>
            <label for="department">Department:</label>
            <select name="department" id="department">
                <option value="">-- Select Department --</option>
                <option value="1">IT Department</option>
                <option value="2">Health Department</option>
                <option value="3">Education Department</option>
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

    </form>
</body>

</html>