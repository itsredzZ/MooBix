coba navbar.php:

<?php
// NAVBAR UNIVERSAL - CINETIX THEATER
// Cek apakah variabel $isHomePage sudah diset di halaman pemanggil
$isHomePage = isset($isHomePage) ? $isHomePage : false;

// Data user dari session
$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole = $_SESSION['user_role'] ?? 'user';
$isAdmin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
$isLoggedIn = isset($_SESSION['user_id']);

// Deteksi halaman aktif untuk class "active"
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<header id="navbar">
    <div class="logo" onclick="window.location.href='ui_index.php'" style="cursor:pointer;">
        MOOBIX THEATER
    </div>
    
    <nav class="main-nav">
        <?php if($isHomePage && !$isAdmin): ?>
            <a href="#hero-section">NEWEST HIT</a>
            <a href="#schedule-section">MORE FILMS</a>
        <?php elseif(!$isHomePage): ?>
            
        <?php endif; ?>
    </nav>
    
    <div class="login-area">
        <?php if(!$isAdmin && $isHomePage): ?>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search film...">
            </div>
        <?php endif; ?>

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
                            <span class="user-role-badge">
                                <?php echo $isAdmin ? 'Administrator' : 'Regular User'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="divider-mini"></div>
                    
                    <?php if($isAdmin): ?>
                        <div class="admin-section-label" style="padding: 5px 15px; font-size: 10px; color: #888;">ADMIN MANAGEMENT</div>
                        <a href="ui_index.php"><i class="ph ph-gear"></i> Admin Panel</a>
                        <a href="user_manage.php"><i class="ph ph-users"></i> Manage Users</a>
                        <a href="manage_bookings.php"><i class="ph ph-calendar-check"></i> Bookings</a>
                        
                        <div class="divider-mini"></div>
                    <?php endif; ?>
                    <div class="divider-mini"></div>
                    <a href="ui_index.php" class="logout-btn">
                        <i class="ph ph-sign-out"></i> Logout
                    </a>
                </div>
            </div>
        <?php else: ?>
            <button class="login-btn" onclick="window.location.href='login.php'">LOGIN</button>
        <?php endif; ?>
    </div>
</header>