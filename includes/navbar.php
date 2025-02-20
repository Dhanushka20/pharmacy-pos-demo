<nav class="bg-white/70 backdrop-blur-2xl shadow-sm sticky top-0 z-50 border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo Section -->
            <div class="flex items-center gap-2">
                <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-50 transition-all duration-200">
                    <div class="bg-gradient-to-tr from-blue-600 to-blue-500 text-white p-2 rounded-xl shadow-lg shadow-blue-500/20">
                        <i class="fas fa-clinic-medical text-xl"></i>
                    </div>
                    <span class="text-blue-600 font-bold text-lg">Pharmacy POS</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden lg:flex items-center gap-4">
                <a href="index.php" 
                   class="flex items-center gap-2 px-4 py-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200
                         <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>

                <a href="sales.php" 
                   class="flex items-center gap-2 px-4 py-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200
                         <?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sales</span>
                </a>

                <a href="medicines.php" 
                   class="flex items-center gap-2 px-4 py-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200
                         <?php echo basename($_SERVER['PHP_SELF']) == 'medicines.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                    <i class="fas fa-pills"></i>
                    <span>Medicines</span>
                </a>

                <a href="purchases.php" 
                   class="flex items-center gap-2 px-4 py-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200
                         <?php echo basename($_SERVER['PHP_SELF']) == 'purchases.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                    <i class="fas fa-shopping-basket"></i>
                    <span>Purchases</span>
                </a>

                <a href="suppliers.php" 
                   class="flex items-center gap-2 px-4 py-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200
                         <?php echo basename($_SERVER['PHP_SELF']) == 'suppliers.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                    <i class="fas fa-truck"></i>
                    <span>Suppliers</span>
                </a>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="backup.php" 
                   class="flex items-center gap-2 px-4 py-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200
                         <?php echo basename($_SERVER['PHP_SELF']) == 'backup.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                    <i class="fas fa-database"></i>
                    <span>Backup</span>
                </a>
                <?php endif; ?>
            </div>

            <!-- User Menu -->
            <div class="relative flex items-center gap-2">
                <!-- User Profile Button -->
                <button type="button" id="userMenuButton" 
                        class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 transition-all duration-200">
                    <div class="bg-gradient-to-tr from-blue-600 to-blue-500 text-white p-2 rounded-lg shadow-lg shadow-blue-500/20">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="text-gray-700">
                        <span class="font-medium"><?php echo $_SESSION['username']; ?></span>
                        <i class="fas fa-chevron-down ml-1 text-gray-400"></i>
                    </div>
                </button>

                <!-- User Dropdown Menu -->
                <div id="userMenu" 
                     class="hidden absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900"><?php echo $_SESSION['username']; ?></p>
                        <p class="text-xs text-gray-500"><?php echo ucfirst($_SESSION['role']); ?></p>
                    </div>
                    <a href="logout.php" 
                       class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-all duration-200">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Logout</span>
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button type="button" 
                        class="lg:hidden mobile-menu-button p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-all duration-200">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu hidden lg:hidden absolute inset-x-0 top-16 bg-white border-b border-gray-100 z-40">
        <div class="px-4 py-2 space-y-1">
            <a href="index.php" class="flex items-center gap-2 p-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200">
                <i class="fas fa-chart-pie w-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="sales.php" class="flex items-center gap-2 p-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200">
                <i class="fas fa-shopping-cart w-5"></i>
                <span>Sales</span>
            </a>
            <a href="medicines.php" class="flex items-center gap-2 p-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200">
                <i class="fas fa-pills w-5"></i>
                <span>Medicines</span>
            </a>
            <a href="purchases.php" class="flex items-center gap-2 p-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200">
                <i class="fas fa-shopping-basket w-5"></i>
                <span>Purchases</span>
            </a>
            <a href="suppliers.php" class="flex items-center gap-2 p-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200">
                <i class="fas fa-truck w-5"></i>
                <span>Suppliers</span>
            </a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="backup.php" class="flex items-center gap-2 p-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200">
                <i class="fas fa-database w-5"></i>
                <span>Backup</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
// User Menu
const userMenuButton = document.getElementById('userMenuButton');
const userMenu = document.getElementById('userMenu');
let isUserMenuOpen = false;

function toggleUserMenu() {
    isUserMenuOpen = !isUserMenuOpen;
    userMenu.classList.toggle('hidden');
}

function closeUserMenu() {
    if (isUserMenuOpen) {
        isUserMenuOpen = false;
        userMenu.classList.add('hidden');
    }
}

userMenuButton.addEventListener('click', (e) => {
    e.stopPropagation();
    toggleUserMenu();
});

// Mobile Menu
const mobileMenuButton = document.querySelector('.mobile-menu-button');
const mobileMenu = document.querySelector('.mobile-menu');
let isMobileMenuOpen = false;

function toggleMobileMenu() {
    isMobileMenuOpen = !isMobileMenuOpen;
    mobileMenu.classList.toggle('hidden');
}

function closeMobileMenu() {
    if (isMobileMenuOpen) {
        isMobileMenuOpen = false;
        mobileMenu.classList.add('hidden');
    }
}

mobileMenuButton.addEventListener('click', (e) => {
    e.stopPropagation();
    toggleMobileMenu();
});

// Close menus when clicking outside
document.addEventListener('click', (e) => {
    if (!userMenu.contains(e.target)) {
        closeUserMenu();
    }
    if (!mobileMenu.contains(e.target)) {
        closeMobileMenu();
    }
});

// Close menus on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeUserMenu();
        closeMobileMenu();
    }
});

// Close menus on window resize
window.addEventListener('resize', () => {
    closeUserMenu();
    if (window.innerWidth >= 1024) {
        closeMobileMenu();
    }
});
</script>