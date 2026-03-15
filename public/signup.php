<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tell PHP to store sessions in a local folder instead of the system temp folder
$sessionPath = __DIR__ . '/../sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

session_start();

require_once '../config/config.php';

// Fetch Departments and Roles for the dropdowns
// Note: Changed to match your SQL dump column names (dept_id, dept_name, etc.)
$depts = $conn->query("SELECT dept_id, dept_name FROM department");
$roles = $conn->query("SELECT user_role_id, role_name FROM user_role");
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- FIX 1: Add a version number (?v=1) to force the browser to reload the CSS -->
    <link href="../dist/output.css?v=<?php echo time(); ?>" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>PRC DTS - Create Account</title>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center font-sans p-4">

    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden border border-slate-100">
        <div class="h-2 bg-blue-900"></div>

        <div class="p-8">
            <div class="text-center mb-8">
                <!-- FIX 2: Ensure prclogo.png is inside the public folder -->
                <img src="img/prclogo.png" alt="PRC Logo" class="h-16 w-16 mx-auto mb-2">
                <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tight">System Registration</h1>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">Create your personnel account</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div
                    class="mb-6 p-3 rounded-xl bg-red-50 border border-red-100 text-red-600 text-xs font-bold flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="../controllers/signup_handler.php" method="POST"
                class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Personal Info -->
                <div class="space-y-4">
                    <h3
                        class="text-blue-900 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 pb-2">
                        Personal Details</h3>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">First Name</label>
                        <input type="text" name="first_name" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Last Name</label>
                        <input type="text" name="last_name" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Birthdate</label>
                        <input type="date" name="birthdate" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Official
                            Email</label>
                        <input type="email" name="email" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-sm">
                    </div>
                </div>

                <!-- Account Info -->
                <div class="space-y-4">
                    <h3
                        class="text-blue-900 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 pb-2">
                        Account Security</h3>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Department</label>
                        <!-- Added Styling to Select -->
                        <select name="dept_id" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-blue-100 outline-none text-sm appearance-none">
                            <option value="" disabled selected>Select Department</option>
                            <?php while ($d = $depts->fetch_assoc()): ?>
                                <option value="<?= $d['dept_id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Role</label>
                        <!-- Added Styling to Select -->
                        <select name="role_id" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-blue-100 outline-none text-sm appearance-none">
                            <option value="" disabled selected>Select Role</option>
                            <?php while ($r = $roles->fetch_assoc()): ?>
                                <option value="<?= $r['user_role_id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Username</label>
                        <input type="text" name="username" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-sm">
                    </div>
                </div>

                <div class="md:col-span-2 pt-4">
                    <button type="submit"
                        class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-4 rounded-2xl shadow-lg active:scale-[0.98] transition-all uppercase text-xs tracking-widest">
                        Register Account
                    </button>
                    <p class="text-center text-xs text-slate-500 mt-4">
                        Already have an account? <a href="index.php"
                            class="text-blue-700 font-bold hover:underline">Sign In</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

</body>

</html>