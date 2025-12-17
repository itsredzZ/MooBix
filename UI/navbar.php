<?php
// File: navbar.php
// NAVBAR UNIVERSAL untuk semua halaman

// Cek apakah variabel $isHomePage sudah diset
$isHomePage = isset($isHomePage) ? $isHomePage : false;

// Data user dari session
$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole = $_SESSION['user_role'] ?? 'user';
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$isLoggedIn = isset($_SESSION['user_name']);

// Deteksi halaman aktif
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<header id="navbar">
    <div class="logo">CINETIX THEATER</div>
    
    <?php if($isHomePage): ?>
        <!-- NAVBAR FULL untuk Home Page -->
        <nav class="main-nav">
            <a href="ui_index.php#now-showing">Now Showing</a>
            <a href="ui_index.php#coming-soon">Coming Soon</a>
            <a href="ui_index.php#about">About</a>
            <a href="ui_index.php#contact">Contact</a>
        </nav>
    <?php else: ?>
        <!-- NAVBAR MINIMALIS untuk non-Home Pages -->
        <nav class="main-nav">
            <!-- Kosongkan atau isi dengan link yang relevan -->
        </nav>
    <?php endif; ?>
    
    <div class="login-area">
        <?php if($isLoggedIn): ?>
            <div class="profile-dropdown">
                <div class="profile-trigger">
                    <div class="avatar"><?php echo strtoupper(substr($userName, 0, 1)); ?></div>
                    <span class="username"><?php echo htmlspecialchars($userName); ?></span>
                    <i class="ph ph-caret-down"></i>
                </div>
                <div class="dropdown-menu">
                    <div class="profile-header">
                        <div class="profile-avatar-large"><?php echo strtoupper(substr($userName, 0, 1)); ?></div>
                        <div class="profile-info">
                            <h4><?php echo htmlspecialchars($userName); ?></h4>
                            <p><?php echo htmlspecialchars($userEmail); ?></p>
                            <span class="user-role-badge"><?php echo $isAdmin ? 'Administrator' : 'Regular User'; ?></span>
                        </div>
                    </div>
                    
                    <div class="divider-mini"></div>
                    
                    <a href="ui_index.php" class="<?php echo ($currentPage == 'ui_index.php') ? 'active' : ''; ?>">
                        <i class="ph ph-house"></i> Home/Beranda
                    </a>
                    <a href="my_tickets.php" class="<?php echo ($currentPage == 'my_tickets.php') ? 'active' : ''; ?>">
                        <i class="ph ph-ticket"></i> Tiket Saya
                    </a>
                    <a href="transaction_history.php" class="<?php echo ($currentPage == 'transaction_history.php') ? 'active' : ''; ?>">
                        <i class="ph ph-clock-counter-clockwise"></i> Riwayat Transaksi
                    </a>
                    <a href="edit_profile.php" class="<?php echo ($currentPage == 'edit_profile.php') ? 'active' : ''; ?>">
                        <i class="ph ph-pencil-simple"></i> Edit Profil
                    </a>
                    
                    <?php if($isAdmin): ?>
                        <div class="divider-mini"></div>
                        <a href="admin.php">
                            <i class="ph ph-gear"></i> Admin Dashboard
                        </a>
                    <?php endif; ?>
                    
                    <div class="divider-mini"></div>
                    <a href="logout.php" class="logout-btn">
                        <i class="ph ph-sign-out"></i> Logout
                    </a>
                </div>
            </div>
        <?php else: ?>
            <button class="login-btn" onclick="window.location.href='login.php'">LOGIN</button>
        <?php endif; ?>
    </div>
</header>