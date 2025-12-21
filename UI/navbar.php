<?php
// Pastikan session dimulai jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. GUNAKAN LOGIKA YANG SAMA DENGAN functions.php AGAR KONSISTEN
// (Atau lebih baik lagi jika Anda include 'functions.php' di sini)
$rawRole = $_SESSION['user_role'] ?? '';
$cleanRole = strtolower(trim($rawRole));

$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$isAdmin = ($cleanRole === 'admin'); // Logic Admin yang kuat
$isLoggedIn = isset($_SESSION['user_id']);

// Default isHomePage ke false jika tidak diset
$isHomePage = isset($isHomePage) ? $isHomePage : false;
?>

<header id="navbar">
    <div class="logo" onclick="window.location.href='ui_index.php'" style="cursor:pointer;">
        MOOBIX THEATER
    </div>

    <nav class="main-nav">
        <?php if (!$isAdmin): ?>
            <?php if ($isHomePage): ?>
                <a href="#hero-section">NEWEST HIT</a>
                <a href="#schedule-section">MORE FILMS</a>
            <?php else: ?>
                <a href="ui_index.php#hero-section">NEWEST HIT</a>
                <a href="ui_index.php#schedule-section">MORE FILMS</a>
            <?php endif; ?>
        <?php endif; ?>
        
        </nav>

    <div class="login-area">
        <?php if (!$isAdmin && $isHomePage): ?>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search film...">
            </div>
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
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

                    <?php if ($isAdmin): ?>
                        <div style="padding: 5px 20px; font-size: 10px; color: #888; font-weight:bold; letter-spacing:1px;">ADMIN TOOLS</div>
                        
                        <a href="ui_index.php"><i class="ph ph-gear"></i> Dashboard</a> 
                        
                        <a href="user_manage.php"><i class="ph ph-users"></i> Manage Users</a>
                        <a href="manage_bookings.php"><i class="ph ph-calendar-check"></i> Manage Bookings</a>
                        <div class="divider-mini"></div>
                        <a href="../Authentication/logout.php" class="logout-btn">
                            <i class="ph ph-sign-out"></i> Logout
                        </a>

                    <?php else: ?>
                        <a href="ui_index.php"><i class="ph ph-house"></i> Home/Beranda</a>
                        <a href="my_tickets.php"><i class="ph ph-ticket"></i> My Tickets</a>
                        <a href="transaction_history.php"><i class="ph ph-clock-counter-clockwise"></i> History</a>
                        <a href="edit_profile.php"><i class="ph ph-pencil-simple"></i> Edit Profile</a>

                        <div class="divider-mini"></div>
                        <a href="../Authentication/logout.php" class="logout-btn">
                            <i class="ph ph-sign-out"></i> Logout
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <button class="login-btn" onclick="showModal('loginModal')">LOGIN</button>
        <?php endif; ?>
    </div>
</header>