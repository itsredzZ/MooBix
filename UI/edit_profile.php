<?php
// File: edit_profile.php
session_start();
require 'db.php';

// ==========================================
// SET NAVBAR TYPE (MINIMALIS UNTUK NON-HOME)
// ==========================================
$isHomePage = false; // <-- INI PENTING untuk navbar minimalis

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login_process.php');
    exit();
}

// ==========================================
// VARIABEL USER
// ==========================================
$user_id = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole = $_SESSION['user_role'] ?? 'user';
$isLoggedIn = isset($_SESSION['user_name']);
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// ==========================================
// AMBIL DATA USER DARI DATABASE
// ==========================================
$user_data = [];
$error_message = '';
$success_message = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        $error_message = "Data pengguna tidak ditemukan!";
    }
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// ==========================================
// PROSES UPDATE PROFILE
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Validasi
    if (empty($username) || empty($email)) {
        $error_message = "Username dan email wajib diisi!";
    } else {
        try {
            // Cek apakah username sudah digunakan
            $stmt = $pdo->prepare("SELECT id FROM users WHERE name = ? AND id != ?");
            $stmt->execute([$username, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                $error_message = "Username sudah digunakan oleh pengguna lain!";
            } else {
                // Cek apakah email sudah digunakan
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $user_id]);
                
                if ($stmt->rowCount() > 0) {
                    $error_message = "Email sudah digunakan oleh pengguna lain!";
                } else {
                    // Update data user
                    $sql = "UPDATE users SET 
                            name = ?,
                            email = ?,
                            updated_at = NOW()
                            WHERE id = ?";
                    
                    $stmt = $pdo->prepare($sql);
                    $success = $stmt->execute([$username, $email, $user_id]);
                    
                    if ($success) {
                        // Update session data
                        $_SESSION['user_name'] = $username;
                        $_SESSION['user_email'] = $email;
                        
                        $success_message = "Profil berhasil diperbarui!";
                        
                        // Refresh user data
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Update variabel
                        $userName = $username;
                        $userEmail = $email;
                    } else {
                        $error_message = "Gagal memperbarui profil!";
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// ==========================================
// PROSES UPDATE PASSWORD
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Semua field password wajib diisi!";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Password baru tidak cocok!";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Password baru minimal 6 karakter!";
    } else {
        try {
            // Ambil data user untuk verifikasi password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($current_password, $user['password'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
                
                $stmt = $pdo->prepare($sql);
                $success = $stmt->execute([$hashed_password, $user_id]);
                
                if ($success) {
                    $success_message = "Password berhasil diperbarui!";
                } else {
                    $error_message = "Gagal memperbarui password!";
                }
            } else {
                $error_message = "Password saat ini salah!";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// ==========================================
// FUNGSI HELPER
// ==========================================
function safe($array, $key, $default = '') {
    return isset($array[$key]) ? htmlspecialchars($array[$key]) : $default;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - MooBix</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Dancing+Script:wght@700&family=Oswald:wght@300;400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <link rel="stylesheet" href="ui_style.css">
</head>
<body>

    <!-- ==========================================
        INCLUDE UNIVERSAL NAVBAR
    ========================================== -->
    <?php include 'navbar.php'; ?>

    <div class="profile-container">
        <div class="page-header">
            <h1><i class="ph ph-user-circle"></i> Edit Profil</h1>
            <p>Kelola informasi akun Anda</p>
        </div>
        
        <!-- MESSAGES -->
        <?php if ($error_message): ?>
            <div class="message error-message">
                <i class="ph ph-warning-circle"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="message success-message">
                <i class="ph ph-check-circle"></i>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="profile-content">
            <!-- USER AVATAR -->
            <div class="user-avatar">
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($user_data['name'] ?? $userName, 0, 1)); ?>
                </div>
                <h3><?php echo htmlspecialchars($user_data['name'] ?? $userName); ?></h3>
                <p><?php echo htmlspecialchars($user_data['email'] ?? $userEmail); ?></p>
                <span class="user-badge">
                    <?php echo ($user_data['role'] ?? $userRole) == 'admin' ? 'Administrator' : 'Pengguna'; ?>
                </span>
            </div>
            
            <!-- INFORMASI PROFIL -->
            <h2 class="section-title">
                <i class="ph ph-user"></i>
                Informasi Akun
            </h2>
            
            <form method="POST" action="" id="profileForm">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="ph ph-user"></i>
                        Username
                    </label>
                    <input type="text" 
                           name="username" 
                           class="form-input" 
                           value="<?php echo safe($user_data, 'name', $userName); ?>"
                           required
                           placeholder="Masukkan username baru">
                    <span id="username-error" style="color:#ff6b6b; font-size:0.8rem; margin-top:5px; display:block;"></span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="ph ph-envelope"></i>
                        Alamat Email
                    </label>
                    <input type="email" 
                           name="email" 
                           class="form-input" 
                           value="<?php echo safe($user_data, 'email', $userEmail); ?>"
                           required
                           placeholder="email@example.com">
                    <span id="email-error" style="color:#ff6b6b; font-size:0.8rem; margin-top:5px; display:block;"></span>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="ph ph-floppy-disk"></i>
                        Simpan Perubahan
                    </button>
                    <button type="button" class="btn-cancel-form" onclick="window.location.href='ui_index.php'">
                        <i class="ph ph-x"></i>
                        Batal
                    </button>
                </div>
            </form>
            
            <!-- UBAH PASSWORD -->
            <h2 class="section-title">
                <i class="ph ph-lock"></i>
                Keamanan Akun
            </h2>
            
            <form method="POST" action="" id="passwordForm">
                <input type="hidden" name="update_password" value="1">
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="ph ph-lock-key"></i>
                        Password Saat Ini
                    </label>
                    <div class="password-wrapper">
                        <input type="password" 
                               name="current_password" 
                               class="form-input" 
                               placeholder="Masukkan password saat ini"
                               required>
                        <button type="button" class="toggle-password">
                            <i class="ph ph-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="ph ph-lock-simple"></i>
                        Password Baru
                    </label>
                    <div class="password-wrapper">
                        <input type="password" 
                               name="new_password" 
                               class="form-input" 
                               placeholder="Minimal 6 karakter"
                               required>
                        <button type="button" class="toggle-password">
                            <i class="ph ph-eye"></i>
                        </button>
                    </div>
                    <span id="password-strength" style="font-size:0.8rem; margin-top:5px; display:block;"></span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="ph ph-lock-simple"></i>
                        Konfirmasi Password
                    </label>
                    <div class="password-wrapper">
                        <input type="password" 
                               name="confirm_password" 
                               class="form-input" 
                               placeholder="Ulangi password baru"
                               required>
                        <button type="button" class="toggle-password">
                            <i class="ph ph-eye"></i>
                        </button>
                    </div>
                    <span id="password-match" style="font-size:0.8rem; margin-top:5px; display:block;"></span>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="ph ph-key"></i>
                        Ubah Password
                    </button>
                </div>
            </form>
            
            <!-- BACK TO HOME -->
            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); text-align: center;">
                <a href="ui_index.php" style="color: rgba(243, 239, 224, 0.7); text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <i class="ph ph-arrow-left"></i>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- ==========================================
        INCLUDE UNIVERSAL FOOTER
    ========================================== -->
    <?php include 'footer.php'; ?>
    
    <!-- ==========================================
        INCLUDE JAVASCRIPT DARI ui_script.js
        (Semua fungsi sudah ada di ui_script.js)
    ========================================== -->
    <script src="ui_script.js"></script>
</body>
</html>