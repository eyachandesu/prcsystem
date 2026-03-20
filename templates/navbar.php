<?php
// Ensure $isLoggedIn is defined even if not set by the parent page
$isLoggedIn = $isLoggedIn ?? isset($_COOKIE['auth_token']);
?>

<!-- Navbar Styles -->
<style>
    /* Smooth transition for the mobile menu expansion */
    #mobile-menu {
        transition: all 0.3s ease-in-out;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
    }

    #mobile-menu.open {
        max-height: 500px;
        /* Large enough to fit content */
        opacity: 1;
        margin-top: 1rem;
    }
</style>

<!-- Navbar Component -->
<?php
// Ensure $isLoggedIn is defined
$isLoggedIn = $isLoggedIn ?? isset($_COOKIE['auth_token']);
?>

<!-- Navbar Component -->
<div class="fixed top-0 left-0 right-0 z-50 p-4">
    <nav class="max-w-6xl mx-auto bg-white/80 backdrop-blur-md border border-slate-200/60 shadow-xl rounded-2xl px-4">
        <!-- 
            Main Grid Container:
            - grid-cols-3: Splits the bar into 3 equal sections
            - items-center: Vertically centers all items
        -->
<div class="grid grid-cols-3 items-center h-20 w-full px-6">
            <!-- Column 1: Logo (Left Aligned) -->
            <div class="flex-1 flex justify-start">
                <a href="/public/admin_dashboard.php" class="flex items-center space-x-3 group">
                    <div class="bg-blue-600 p-2 rounded-lg group-hover:rotate-12 transition-transform duration-300">
                        <img src="/public/img/prclogo.png" alt="PRC" class="h-10 w-10 brightness-0 invert">
                    </div>
                    <span class="text-mg font-bold text-slate-800 tracking-tight uppercase  sm:inline">
                        PRC <span class="text-blue-600">Tracking</span>
                    </span>
                </a>
            </div>

            <!-- Column 2: Nav Links (Centered via Grid) -->
            <div class=" md:flex gap-2 lg:gap-5 justify-center items-center">
                <a href="/public/admin_dashboard.php"
                    class="text-slate-600 hover:text-blue-600 font-medium px-3 py-2 rounded-xl transition-all duration-200 hover:bg-blue-50/50 text-sm lg:text-base">Dashboard</a>
                <a href="/public/tracking.php"
                    class="text-slate-600 hover:text-blue-600 font-medium px-3 py-2 rounded-xl transition-all duration-200 hover:bg-blue-50/50 text-sm lg:text-base">Tracking</a>
                <a href="/public/receive.php"
                    class="text-slate-600 hover:text-blue-600 font-medium px-3 py-2 rounded-xl transition-all duration-200 hover:bg-blue-50/50 text-sm lg:text-base">Receive</a>
                <a href="/public/transfer.php"
                    class="text-slate-600 hover:text-blue-600 font-medium px-3 py-2 rounded-xl transition-all duration-200 hover:bg-blue-50/50 text-sm lg:text-base">Transfer</a>
            </div>

            <!-- Column 3: Auth Button (Right Aligned) -->
            <div class=" md:flex gap-2 lg:gap-4 justify-end items-center ">
                <div class="hidden lg:block h-6 w-px bg-slate-200 mx-2 "></div>

                <?php if ($isLoggedIn): ?>
                    <a href="/controllers/logout_handler.php"
                        class="text-red-500 hover:text-red-600 font-semibold p-4 py-2 rounded-xl transition-all duration-200 hover:bg-red-50 flex items-center text-sm">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        <span class="sm:inline">Logout</span>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </nav>
</div>

<!-- Mobile Menu Script -->
<script>
    (function () {
        const button = document.getElementById('mobile-menu-button');
        const menu = document.getElementById('mobile-menu');
        const icon = document.getElementById('menu-icon');

        if (!button || !menu || !icon) return;

        function toggleMenu() {
            const isOpen = menu.classList.contains('open');
            if (isOpen) {
                menu.classList.remove('open');
                icon.classList.replace('fa-times', 'fa-bars');
            } else {
                menu.classList.add('open');
                icon.classList.replace('fa-bars', 'fa-times');
            }
        }

        button.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleMenu();
        });

        document.addEventListener('click', function (event) {
            if (!menu.contains(event.target) && !button.contains(event.target)) {
                menu.classList.remove('open');
                icon.classList.replace('fa-times', 'fa-bars');
            }
        });
    })();
</script>