<?php
// ==========================================
// BACKEND: LOGIKA MANAGE USERS
// ==========================================

// Cek apakah session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek koneksi database
if (!isset($pdo)) {
    require_once 'db.php';
}

// Cek User Login (Session Check)
$userName = $_SESSION['user_name'] ?? 'Admin';
$userEmail = $_SESSION['user_email'] ?? 'admin@moobix.com';

// --- LOGIKA PENGAMBILAN DATA USERS ---
$usersList = [];
$totalUsers = 0;
$activeUsers = 0;
$adminsCount = 0;

try {
    if (isset($pdo)) {
        // 1. Setup Pagination
        $limit = 10; 
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        // 2. Hitung Total Data (untuk pagination)
        $stmtCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
        $totalUsers = $stmtCount->fetchColumn();
        $totalPages = ceil($totalUsers / $limit);

        // 3. Ambil Data User (sesuai halaman)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'user' ORDER BY id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $usersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 4. Hitung Statistik
        $stmtStats = $pdo->query("SELECT status, role FROM users");
        $allUsers = $stmtStats->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($allUsers as $u) {
            if ($u['status'] == 'active') $activeUsers++;
            if ($u['role'] == 'admin') $adminsCount++;
        }
    }
} catch (Exception $e) {
    // Silent error
}

// --- HELPER FUNCTIONS ---
if (!function_exists('formatDate')) {
    function formatDate($dateString) {
        if (empty($dateString) || $dateString == '0000-00-00 00:00:00') return '-';
        $date = new DateTime($dateString);
        return $date->format('d M Y H:i');
    }
}

if (!function_exists('safe')) {
    function safe($array, $key, $default = '-') {
        return isset($array[$key]) ? htmlspecialchars($array[$key]) : $default;
    }
}

function getRoleColor($role) {
    switch($role) {
        case 'admin': return '#d32f2f';
        case 'user': return '#4caf50';
        default: return '#607d8b';
    }
}

function getStatusColor($status) {
    switch($status) {
        case 'active': return '#4caf50';
        case 'inactive': return '#f44336';
        default: return '#607d8b';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Moobix Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Dancing+Script:wght@700&family=Oswald:wght@300;400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <link rel="stylesheet" href="ui_style.css">
    
    <style>
        /* Perbaikan kecil agar Navbar tidak menumpuk konten */
        body {
            font-family: 'Oswald', sans-serif; /* Pakai font Oswald sebagai default body */
            background-color: #f9f9f9;
        }
        
        /* Pastikan font judul menggunakan font keren (Bebas Neue/Playfair) */
        h2, h3, .logo {
            font-family: 'Bebas Neue', cursive; 
            letter-spacing: 1px;
        }
        
        .section-header span {
            font-family: 'Dancing Script', cursive;
            font-size: 1.5rem;
            color: #d32f2f;
        }

        /* Tambahan style khusus halaman ini */
        .search-container {
            position: relative;
            margin-right: 15px;
        }
    </style>
</head>
<body>

    <?php 
    // Set variabel agar navbar tahu ini bukan Home Page user biasa
    $isHomePage = false; 
    include 'navbar.php'; //
    ?>

    <main id="admin-dashboard" style="padding-top: 100px;">
        <div class="section-header">
            <span>👥 Admin Control Center</span>
            <h2>USER MANAGEMENT PANEL</h2>
        </div>
        
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            
            <div style="display: flex; align-items: center; margin-bottom: 30px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(170, 43, 43, 0.1);">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; margin-right: 25px;">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
                <div style="flex: 1;">
                    <h3 style="margin: 0; color: #333; font-size: 28px;">Welcome back, <?php echo htmlspecialchars($userName); ?>! 👋</h3>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Last login: <?php echo date('d M Y H:i', $_SESSION['login_time'] ?? time()); ?></p>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; color: #666; font-size: 14px;">Role: <span style="background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: bold;">ADMINISTRATOR</span></p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 40px;">
                <div style="background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(170, 43, 43, 0.3);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">👥 Total Users</h4>
                            <p style="font-size: 36px; font-weight: bold; margin: 0;"><?php echo $totalUsers; ?></p>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="ph ph-users-three"></i>
                        </div>
                    </div>
                    <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;"><?php echo $activeUsers; ?> active, <?php echo $adminsCount; ?> admins</p>
                </div>
                
                <div style="background: linear-gradient(135deg, #c62828, #b71c1c); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(198, 40, 40, 0.3);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">✅ Active Users</h4>
                            <p style="font-size: 36px; font-weight: bold; margin: 0;"><?php echo $activeUsers; ?></p>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="ph ph-user-circle-check"></i>
                        </div>
                    </div>
                    <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;"><?php echo $totalUsers > 0 ? round(($activeUsers/$totalUsers)*100, 1) : 0; ?>% of total users</p>
                </div>
                
                <div style="background: linear-gradient(135deg, #d32f2f, #aa2b2b); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(211, 47, 47, 0.3);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">👑 Admin Users</h4>
                            <p style="font-size: 36px; font-weight: bold; margin: 0;"><?php echo $adminsCount; ?></p>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="ph ph-crown"></i>
                        </div>
                    </div>
                    <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;"><?php echo $totalUsers - $adminsCount; ?> regular users</p>
                </div>
                
                <div style="background: linear-gradient(135deg, #e53935, #c62828); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(229, 57, 53, 0.3);">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">📊 User Activity</h4>
                            <p style="font-size: 36px; font-weight: bold; margin: 0;">156</p>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="ph ph-activity"></i>
                        </div>
                    </div>
                    <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;">Active sessions last 24h</p>
                </div>
            </div>
            
            <div style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 20px rgba(170, 43, 43, 0.1); margin-bottom: 40px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="color: #333; margin: 0; font-size: 24px; display: flex; align-items: center; gap: 10px;">
                        <i class="ph ph-users" style="color: #aa2b2b;"></i>
                        Active Users List
                    </h3>
                    <div style="display: flex; gap: 15px;">
                        <button onclick="refreshUsers()" style="background: #f5f5f5; border: 1px solid #ddd; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.3s;" 
                                onmouseover="this.style.background='#e9e9e9';" 
                                onmouseout="this.style.background='#f5f5f5';">
                            <i class="ph ph-arrows-clockwise"></i>
                            Refresh
                        </button>
                    </div>
                </div>
                
                <div style="overflow-x: auto;">
                    <table id="usersTable" style="width: 100%; border-collapse: collapse; min-width: 1000px;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #2C1E1C, #1F1514); color: white;">
                                <th onclick="sortTable(0)" style="padding: 15px; text-align: left; border-bottom: 2px solid #444; width: 50px; cursor: pointer;">
                                    ID <i class="ph ph-caret-up-down" style="font-size: 12px; opacity: 0.7;"></i>
                                </th>
                                <th onclick="sortTable(1)" style="padding: 15px; text-align: left; border-bottom: 2px solid #444; cursor: pointer;">
                                    User Info <i class="ph ph-caret-up-down" style="font-size: 12px; opacity: 0.7;"></i>
                                </th>
                                <th onclick="sortTable(2)" style="padding: 15px; text-align: left; border-bottom: 2px solid #444; cursor: pointer;">
                                    Role <i class="ph ph-caret-up-down" style="font-size: 12px; opacity: 0.7;"></i>
                                </th>
                                <th onclick="sortTable(3)" style="padding: 15px; text-align: left; border-bottom: 2px solid #444; cursor: pointer;">
                                    Status <i class="ph ph-caret-up-down" style="font-size: 12px; opacity: 0.7;"></i>
                                </th>
                                <th onclick="sortTable(4)" style="padding: 15px; text-align: left; border-bottom: 2px solid #444; cursor: pointer;">
                                    Created At <i class="ph ph-caret-up-down" style="font-size: 12px; opacity: 0.7;"></i>
                                </th>
                                <th onclick="sortTable(5)" style="padding: 15px; text-align: left; border-bottom: 2px solid #444; cursor: pointer;">
                                    Last Login <i class="ph ph-caret-up-down" style="font-size: 12px; opacity: 0.7;"></i>
                                </th>
                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444; width: 180px;">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($usersList)): ?>
                                <tr>
                                    <td colspan="7" style="padding: 30px; text-align: center; color: #888;">No users found in database.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($usersList as $user): ?>
                                <tr style="border-bottom: 1px solid #eee; transition: background 0.3s;">
                                    <td style="padding: 15px; color: #666; font-weight: bold;"><?php echo safe($user, 'id'); ?></td>
                                    <td style="padding: 15px;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, <?php echo getRoleColor($user['role'] ?? 'user'); ?>, <?php echo getRoleColor($user['role'] ?? 'user'); ?>80); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">
                                                <?php echo strtoupper(substr(safe($user, 'name'), 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 500; color: #333;"><?php echo safe($user, 'name'); ?></div>
                                                <div style="color: #666; font-size: 13px;"><?php echo safe($user, 'email'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span style="background: <?php echo getRoleColor(safe($user, 'role', 'user')); ?>; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;"><?php echo strtoupper(safe($user, 'role', 'user')); ?></span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span style="background: <?php echo getStatusColor(safe($user, 'status', 'active')); ?>; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;"><?php echo strtoupper(safe($user, 'status', 'active')); ?></span>
                                    </td>
                                    <td style="padding: 15px; color: #666; font-size: 14px;">
                                        <?php echo formatDate(safe($user, 'created_at')); ?>
                                    </td>
                                    <td style="padding: 15px; color: #666; font-size: 14px;">
                                        <?php echo formatDate(safe($user, 'last_login')); ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <div style="display: flex; gap: 8px; justify-content: flex-start;">
                                            <!-- TOMBOL VIEW - MERAH MOOBIX -->
                                        <button onclick="showViewModal(<?php echo safe($user, 'id'); ?>)" 
                                                style="background: linear-gradient(135deg, #2C1E1C, #1F1514); color: white; border: none; padding: 6px 10px; border-radius: 5px; cursor: pointer; font-size: 11px; display: flex; align-items: center; gap: 4px; transition: transform 0.2s;" 
                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 3px 8px rgba(44, 30, 28, 0.3)'" 
                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                            <i class="ph ph-eye" style="font-size: 12px;"></i>
                                            <span>View</span>
                                        </button>
                                        
                                        <!-- TOMBOL DELETE - MERAH MOOBIX GELAP -->
                                        <button onclick="showDeleteUserModal(<?php echo safe($user, 'id'); ?>, '<?php echo addslashes(safe($user, 'username')); ?>', '<?php echo addslashes(safe($user, 'email')); ?>')" 
                                                style="background: linear-gradient(135deg, #c62828, #b71c1c); color: white; border: none; padding: 6px 10px; border-radius: 5px; cursor: pointer; font-size: 11px; display: flex; align-items: center; gap: 4px; transition: transform 0.2s;"
                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 3px 8px rgba(198, 40, 40, 0.3)'" 
                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                            <i class="ph ph-trash" style="font-size: 12px;"></i>
                                            <span>Delete</span>
                                        </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                    <div style="color: #666; font-size: 14px;">
                        Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + count($usersList), $totalUsers); ?> of <?php echo $totalUsers; ?> users
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <?php if($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>"><button class="pagination-btn">Previous</button></a>
                        <?php else: ?>
                            <button class="pagination-btn" disabled>Previous</button>
                        <?php endif; ?>

                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>">
                                <button class="pagination-btn <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></button>
                            </a>
                        <?php endfor; ?>

                        <?php if($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>"><button class="pagination-btn">Next</button></a>
                        <?php else: ?>
                            <button class="pagination-btn" disabled>Next</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <!-- ========================================== -->
<!-- MODAL DELETE USER CONFIRMATION - TEMA MOOBIX -->
<!-- ========================================== -->
<div id="deleteUserModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
    <div style="background: linear-gradient(145deg, #ffffff, #f5f5f5); width: 90%; max-width: 500px; border-radius: 20px; padding: 0; overflow: hidden; box-shadow: 0 25px 50px rgba(170, 43, 43, 0.3); border: 1px solid rgba(255,255,255,0.1);">
        <!-- Header dengan efek glassmorphism - TEMA MOOBIX -->
        <div style="background: linear-gradient(135deg, #c62828, #b71c1c); padding: 30px; text-align: center; position: relative;">
            <div style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #c62828; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 20px rgba(198, 40, 40, 0.4);">
                <i class="ph ph-user-minus" style="font-size: 24px; color: white;"></i>
            </div>
            <h2 style="color: white; margin: 20px 0 10px 0; font-size: 26px;">Delete User Account</h2>
            <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 15px;">Permanent action - cannot be undone</p>
        </div>
        
        <div style="padding: 40px 30px 30px; text-align: center;">
            <!-- User Info Card - TEMA MOOBIX -->
            <div style="background: #ffebee; border-radius: 15px; padding: 20px; margin-bottom: 30px; border-left: 5px solid #c62828;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="background: #c62828; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; font-size: 20px;">
                        <span id="deleteUserAvatar">U</span>
                    </div>
                    <div style="text-align: left; flex: 1;">
                        <h4 id="deleteUserName" style="color: #333; margin: 0 0 5px 0; font-size: 18px;"></h4>
                        <p id="deleteUserEmail" style="color: #666; margin: 0 0 5px 0; font-size: 14px;"></p>
                        <p style="color: #666; margin: 0; font-size: 14px;">
                            User ID: <span id="deleteUserId" style="font-weight: bold; color: #c62828;"></span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Warning Box - TEMA MOOBIX -->
            <div style="background: #ffebee; border-radius: 12px; padding: 20px; margin-bottom: 30px; text-align: left;">
                <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 15px;">
                    <div style="color: #c62828; font-size: 20px; margin-top: 2px;">
                        <i class="ph ph-warning-circle"></i>
                    </div>
                    <div>
                        <h4 style="color: #c62828; margin: 0 0 8px 0; font-size: 15px;">What will be deleted?</h4>
                        <ul style="color: #666; padding-left: 20px; margin: 0; font-size: 14px;">
                            <li>User account permanently</li>
                            <li>All user data and preferences</li>
                            <li>Booking history and records</li>
                            <li>Cannot be recovered</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Critical Warning -->
                <div style="background: rgba(198, 40, 40, 0.1); border-radius: 8px; padding: 12px; margin-top: 15px; border-left: 3px solid #c62828;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="color: #c62828; font-size: 18px;">
                            <i class="ph ph-shield-warning"></i>
                        </div>
                        <span style="color: #c62828; font-size: 13px; font-weight: bold;">
                            Warning: This action will permanently remove all user data.
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Confirmation Input -->
            <div style="background: #f8f9fa; border-radius: 12px; padding: 20px; margin-bottom: 30px; text-align: left;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div style="color: #c62828; font-size: 18px;">
                        <i class="ph ph-keyhole"></i>
                    </div>
                    <h4 style="color: #333; margin: 0; font-size: 15px;">Type "DELETE" to confirm</h4>
                </div>
                <input type="text" id="deleteConfirmationInput" placeholder="Type DELETE here..." 
                       style="width: 100%; padding: 12px 15px; border: 2px solid #ffcdd2; border-radius: 8px; font-size: 14px; transition: all 0.3s;"
                       oninput="checkDeleteConfirmation()">
                <p style="color: #ff8a80; font-size: 12px; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
                    <i class="ph ph-info"></i> This is required to proceed with deletion
                </p>
            </div>
            
            <!-- Action Buttons - TEMA MOOBIX -->
            <div style="display: flex; justify-content: center; gap: 15px;">
                <button onclick="closeModal('deleteUserModal')" 
                        style="background: linear-gradient(135deg, #f5f5f5, #e0e0e0); color: #666; border: none; padding: 12px 28px; border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'" 
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <i class="ph ph-x-circle"></i>
                    Cancel
                </button>
                <button id="confirmDeleteBtn" onclick="processUserDelete()" disabled
                        style="background: linear-gradient(135deg, #cccccc, #999999); color: white; border: none; padding: 12px 28px; border-radius: 10px; cursor: not-allowed; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s;">
                    <i class="ph ph-trash-simple"></i>
                    Delete Account
                </button>
            </div>
            
            <p style="color: #ff8a80; font-size: 12px; margin-top: 25px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="ph ph-shield-check"></i>
                This action requires administrator confirmation
            </p>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL VIEW USER DETAILS - TEMA MOOBIX -->
<!-- ========================================== -->
<div id="viewUserModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
    <div style="background: linear-gradient(145deg, #ffffff, #f5f5f5); width: 90%; max-width: 600px; border-radius: 20px; padding: 0; overflow: hidden; box-shadow: 0 25px 50px rgba(170, 43, 43, 0.3); border: 1px solid rgba(255,255,255,0.1);">
        
        <!-- Header - TEMA MOOBIX -->
        <div style="background: linear-gradient(135deg, #2C1E1C, #1F1514); padding: 25px 30px; position: relative;">
            <button onclick="closeModal('viewUserModal')" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); border: none; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; font-size: 20px; transition: background 0.3s;" 
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'" 
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="ph ph-x"></i>
            </button>
            
            <h2 style="color: white; margin: 0; font-size: 24px; display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="ph ph-user" style="font-size: 20px;"></i>
                </div>
                User Details
            </h2>
        </div>
        
        <div style="padding: 30px;">
            <!-- User Profile Card -->
            <div style="background: white; border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(170, 43, 43, 0.08); border: 1px solid #e0e0e0;">
                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold;" id="viewUserAvatar">
                        U
                    </div>
                    <div style="flex: 1;">
                        <h3 id="viewUserName" style="color: #333; margin: 0 0 5px 0; font-size: 24px;"></h3>
                        <p id="viewUserEmail" style="color: #666; margin: 0 0 10px 0; font-size: 14px;"></p>
                        <div style="display: flex; gap: 10px;">
                            <span id="viewUserRole" style="background: #aa2b2b; color: white; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold;">USER</span>
                            <span id="viewUserStatus" style="background: #4caf50; color: white; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold;">ACTIVE</span>
                        </div>
                    </div>
                </div>
                
                <!-- User Stats -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    <div style="text-align: center;">
                        <div style="background: #ffebee; border-radius: 10px; padding: 12px; margin-bottom: 8px;">
                            <i class="ph ph-calendar" style="color: #d32f2f; font-size: 20px;"></i>
                        </div>
                        <span style="color: #666; font-size: 12px; display: block;">Member Since</span>
                        <span id="viewUserCreated" style="color: #333; font-weight: bold; font-size: 14px;">-</span>
                    </div>
                    
                    <div style="text-align: center;">
                        <div style="background: #ffebee; border-radius: 10px; padding: 12px; margin-bottom: 8px;">
                            <i class="ph ph-clock" style="color: #d32f2f; font-size: 20px;"></i>
                        </div>
                        <span style="color: #666; font-size: 12px; display: block;">Last Login</span>
                        <span id="viewUserLastLogin" style="color: #333; font-weight: bold; font-size: 14px;">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                <button onclick="closeModal('viewUserModal')" 
                        style="background: linear-gradient(135deg, #f5f5f5, #e0e0e0); color: #666; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'" 
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <i class="ph ph-x-circle"></i>
                    Close
                </button>
                <button onclick="closeModal('viewUserModal'); showEditUserModal(currentViewingId)" 
                        style="background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(170, 43, 43, 0.4)'" 
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <i class="ph ph-pencil-simple"></i>
                    Edit User
                </button>
            </div>
        </div>
    </div>
</div>
<style>
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
</style>
<script>
// Variabel global
let userToDelete = null;
let currentViewingId = null;

// ==========================================
// FUNGSI UNTUK MODAL DELETE USER
// ==========================================

function showDeleteUserModal(userId, username, email) {
    userToDelete = { id: userId, name: username, email: email };
    
    // Set data ke modal
    document.getElementById('deleteUserId').textContent = userId;
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteUserEmail').textContent = email;
    document.getElementById('deleteUserAvatar').textContent = name.charAt(0).toUpperCase();
    
    // Reset confirmation input
    document.getElementById('deleteConfirmationInput').value = '';
    document.getElementById('confirmDeleteBtn').disabled = true;
    document.getElementById('confirmDeleteBtn').style.background = 'linear-gradient(135deg, #cccccc, #999999)';
    document.getElementById('confirmDeleteBtn').style.cursor = 'not-allowed';
    
    // Tampilkan modal
    const modal = document.getElementById('deleteUserModal');
    modal.style.display = 'flex';
    modal.style.animation = 'fadeIn 0.3s ease';
}

function checkDeleteConfirmation() {
    const input = document.getElementById('deleteConfirmationInput');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    if (input.value.trim().toUpperCase() === 'DELETE') {
        confirmBtn.disabled = false;
        confirmBtn.style.background = 'linear-gradient(135deg, #c62828, #b71c1c)';
        confirmBtn.style.cursor = 'pointer';
        confirmBtn.onmouseover = function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 8px 20px rgba(198, 40, 40, 0.4)';
        };
        confirmBtn.onmouseout = function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        };
    } else {
        confirmBtn.disabled = true;
        confirmBtn.style.background = 'linear-gradient(135deg, #cccccc, #999999)';
        confirmBtn.style.cursor = 'not-allowed';
        confirmBtn.onmouseover = null;
        confirmBtn.onmouseout = null;
    }
}

function processUserDelete() {
    if (!userToDelete) return;
    
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> Deleting...';
    confirmBtn.disabled = true;
    
    // Simulasi proses delete dengan AJAX
    setTimeout(() => {
        // Simulasi response sukses
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; 
            background: linear-gradient(135deg, #c62828, #b71c1c); 
            color: white; padding: 15px 25px; 
            border-radius: 10px; box-shadow: 0 10px 25px rgba(198, 40, 40, 0.3);
            z-index: 1001; animation: slideIn 0.3s ease;
            display: flex; align-items: center; gap: 10px;
        `;
        notification.innerHTML = `
            <i class="ph ph-user-minus" style="font-size: 20px;"></i>
            <div>
                <strong>User Deleted!</strong><br>
                "${userToDelete.name}" has been permanently removed.
            </div>
        `;
        document.body.appendChild(notification);
        
        // Reset button
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
        
        // Hapus notifikasi setelah 3 detik
        setTimeout(() => {
            notification.style.animation = 'fadeIn 0.3s ease reverse';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
        
        // Tutup modal
        closeModal('deleteUserModal');
        
        // Refresh halaman
        setTimeout(() => location.reload(), 1000);
        
    }, 1500);
}

// ==========================================
// FUNGSI UNTUK MODAL VIEW USER
// ==========================================
function showViewModal(userId) {
    currentViewingId = userId;
    
    // Ambil data user dari PHP array
    const users = <?php echo json_encode($usersList); ?>;
    const user = users.find(u => u.id == userId) || {};
    
    // Isi data ke modal
    document.getElementById('viewUserAvatar').textContent = user.name ? user.name.charAt(0).toUpperCase() : 'U';
    document.getElementById('viewUserName').textContent = user.name || 'No Name';
    document.getElementById('viewUserEmail').textContent = user.email || 'No Email';
    
    // Role dan Status
    const role = user.role || 'user';
    const status = user.status || 'active';
    document.getElementById('viewUserRole').textContent = role.toUpperCase();
    document.getElementById('viewUserRole').style.background = getRoleColor(role);
    document.getElementById('viewUserStatus').textContent = status.toUpperCase();
    document.getElementById('viewUserStatus').style.background = getStatusColor(status);
    
    // Tanggal
    document.getElementById('viewUserCreated').textContent = user.created_at ? formatDate(user.created_at) : '-';
    document.getElementById('viewUserLastLogin').textContent = user.last_login ? formatDate(user.last_login) : '-';
    
    // Tampilkan modal
    const modal = document.getElementById('viewUserModal');
    modal.style.display = 'flex';
    modal.style.animation = 'fadeIn 0.3s ease';
}
        document.addEventListener('DOMContentLoaded', function() {
            const profileTrigger = document.querySelector('.profile-trigger');
            if (profileTrigger) {
                profileTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const dropdown = this.nextElementSibling; 
                    if(dropdown) {
                        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                        this.classList.toggle('active');
                    }
                });
            }
            
            window.addEventListener('click', function(e) {
                if (!e.target.closest('.profile-dropdown')) {
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        menu.style.display = 'none';
                    });
                    document.querySelectorAll('.profile-trigger').forEach(trigger => {
                        trigger.classList.remove('active');
                    });
                }
            });
        });

// ==========================================
// FUNGSI UMUM
// ==========================================

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.animation = 'fadeIn 0.3s ease reverse';
    setTimeout(() => {
        modal.style.display = 'none';
        modal.style.animation = '';
    }, 300);
}

function refreshUsers() {
    location.reload();
}


// Tutup modal saat klik di luar modal atau tekan ESC
    
    window.onclick = function(event) {
    const modals = ['deleteUserModal', 'viewUserModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            closeModal(modalId);
        }
    });
};

document.onkeydown = function(event) {
    if (event.key === 'Escape') {
        const modals = ['deleteUserModal', 'viewUserModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal.style.display === 'flex') {
                closeModal(modalId);
            }
        });
    }
};
</script>
</body>
</html>