<?php
// ==========================================
// 1. BACKEND: KONEKSI & LOGIKA GAMBAR LOKAL
// ==========================================
session_start();
require 'db.php';

// --- FOLDER GAMBAR (SESUAIKAN DENGAN STRUKTUR FOLDER KAMU) ---
$local_path = '../ui/uploads/';

// --- FUNGSI SAFETY ---
function safe($array, $key, $default = '-') {
    return isset($array[$key]) ? htmlspecialchars($array[$key]) : $default;
}

// --- FUNGSI CEK GAMBAR (INTERNET VS LOKAL) ---
function getPoster($filename) {
    global $local_path;
    if (empty($filename)) return 'https://via.placeholder.com/400x600?text=No+Image';
    
    if (strpos($filename, 'http') === 0) {
        return $filename;
    } else {
        return $local_path . $filename;
    }
}

// ==========================================
// 1. CEK SESSION USER (DATA DARI LOGIN)
// ==========================================
$current_user = null;
if (isset($_SESSION['user_name'])) {
    $current_user = [
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role'] ?? 'user',
        'email' => $_SESSION['user_email'] ?? '-'
    ];
}

// ==========================================
// 2. PROSES LOGIN (DATABASE VERSION)
// ==========================================
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_form'])) {
    $username = $_POST['username'] ?? ''; 
    $password = $_POST['password'] ?? '';
    
    // Cari user di Database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR name = ?");
    $stmt->execute([$username, $username]);
    
    // SAYA UBAH NAMA VARIABEL DI SINI (DARI $user JADI $user_login)
    $user_login = $stmt->fetch(PDO::FETCH_ASSOC); 

    // Verifikasi Password
    if ($user_login && password_verify($password, $user_login['password'])) {
        // --- LOGIN SUKSES ---
        $_SESSION['user_id'] = $user_login['id'];
        $_SESSION['user_name'] = $user_login['name'];
        $_SESSION['user_email'] = $user_login['email'];
        $_SESSION['user_role'] = $user_login['role'];
        $_SESSION['login_time'] = time();
        
        // Redirect
        if($user_login['role'] == 'admin'){
            header('Location: ui_index.php');
        } else {
            header('Location: ' . $_SERVER['PHP_SELF']);
        }
        exit();
    } else {
        $login_error = "Username atau Password salah!";
    }
}

// ==========================================
// 3. PROSES REGISTER (DATABASE VERSION)
// ==========================================
$register_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_form'])) {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    // Di form kamu name="reg_username" tapi biasanya itu untuk nama juga. 
    // Kita asumsikan fullname = name di database.
    $password = $_POST['reg_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;
    
    // Validasi
    if (empty($fullname) || empty($email) || empty($password)) {
        $register_error = "Semua field wajib diisi!";
    } elseif ($password !== $confirm_password) {
        $register_error = "Password tidak cocok!";
    } elseif (!$terms) {
        $register_error = "Anda harus menyetujui Terms & Policy";
    } else {
        // Cek apakah Email sudah terdaftar?
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmtCheck->execute([$email]);
        
        if ($stmtCheck->rowCount() > 0) {
            $register_error = "Email sudah terdaftar!";
        } else {
            // Masukkan ke Database
            // Password di-hash biar aman
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // Default role

            try {
                $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$fullname, $email, $hashed_password, $role]);
                
                // Auto Login setelah daftar
                $_SESSION['user_name'] = $fullname;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;
                $_SESSION['login_time'] = time();
                
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } catch (PDOException $e) {
                $register_error = "Gagal mendaftar: " . $e->getMessage();
            }
        }
    }
}

// ==========================================
// 4. LOGOUT
// ==========================================
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ui_index.php');
    exit();
}

// ==========================================
// 5. AMBIL DATA FILM (PERBAIKAN ERROR DI SINI)
// ==========================================
$heroMovie = [
    'id' => 0, 'title' => 'DATA TIDAK DITEMUKAN', 'genre' => '-', 
    'price' => 0, 'poster' => '', 'airing_date' => '-',
    'synopsis' => 'Sinopsis belum tersedia.', 'duration' => 'Unknown' 
];
$nowShowing = [];

try {
    // --- SAYA HAPUS BARIS 'new PDO(...)' DISINI ---
    // Kita langsung pakai variabel $pdo yang sudah ada dari 'require db.php'
    
    // 1. HERO MOVIE
    $stmtHero = $pdo->query("SELECT * FROM movies ORDER BY id DESC LIMIT 1");
    $fetchedHero = $stmtHero->fetch(PDO::FETCH_ASSOC);

    if ($fetchedHero) {
        $heroMovie = $fetchedHero;
        $heroMovie['synopsis'] = $heroMovie['synopsis'] ?? 'Sinopsis belum tersedia.';
        $heroMovie['duration'] = $heroMovie['duration'] ?? '2h 0min'; 
    }

    // 2. LIST MOVIE
    if ($heroMovie['id'] != 0) {
        $stmtList = $pdo->prepare("SELECT * FROM movies WHERE id != :id ORDER BY id DESC");
        $stmtList->execute(['id' => $heroMovie['id']]);
        $nowShowing = $stmtList->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) { 
    // Silent Error
}

// Tentukan dashboard mana yang ditampilkan
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$isUser = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user';
$isLoggedIn = isset($_SESSION['user_name']);
$userName = $_SESSION['user_name'] ?? '';
$userRole = $_SESSION['user_role'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MooBix</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Dancing+Script:wght@700&family=Oswald:wght@300;400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <link rel="stylesheet" href="ui_style.css">

    <style>
        /* Modal Styles - DIPERKECIL */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            max-height: 85vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
            z-index: 10;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #333;
        }

        /* Modal Header */
        .modal-header {
            padding: 25px 30px 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .modal-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
        }

        .modal-login-icon {
            background: linear-gradient(135deg, var(--accent-salmon), var(--primary-orange));
            color: white;
        }

        .modal-register-icon {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
        }

        .modal-header h2 {
            color: #2C1E1C;
            margin: 0 0 5px 0;
            font-family: var(--font-head);
            font-size: 1.5rem;
            letter-spacing: 1px;
        }

        .modal-header p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Modal Body */
        .modal-body {
            padding: 20px 30px;
        }

        /* Form Elements - DIPERKECIL */
        .input-group {
            margin-bottom: 16px;
        }

        .input-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #ddd;
            background: white;
            font-family: 'Oswald', sans-serif;
            font-size: 0.95rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            border-color: var(--accent-salmon);
            outline: none;
            box-shadow: 0 0 0 3px rgba(228, 143, 111, 0.1);
        }

        .input-group input::placeholder {
            color: #999;
            font-weight: 300;
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .password-wrapper {
            position: relative;
        }

        /* Buttons - DIPERKECIL */
        .btn-submit {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-family: var(--font-head);
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--accent-salmon), var(--primary-orange));
            color: white;
        }

        .btn-register {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-alt {
            background: transparent;
            border: 1.5px solid #ddd;
            color: #666;
            padding: 10px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            font-family: var(--font-body);
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-alt:hover {
            background: #f5f5f5;
            border-color: #ccc;
        }

        /* Terms Checkbox */
        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            cursor: pointer;
            font-size: 0.8rem;
            color: #666;
            margin: 12px 0;
        }

        .terms-checkbox input {
            margin-right: 8px;
            margin-top: 2px;
        }

        .terms-checkbox a {
            color: var(--accent-salmon);
            text-decoration: none;
        }

        .terms-checkbox a:hover {
            text-decoration: underline;
        }

        /* Error Messages */
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #c62828;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Divider */
        .modal-divider {
            display: flex;
            align-items: center;
            margin: 18px 0;
            color: #888;
            font-size: 0.85rem;
        }

        .modal-divider::before,
        .modal-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #eee;
        }

        .modal-divider span {
            padding: 0 12px;
        }

        /* Switch Form Text */
        .switch-form {
            text-align: center;
            font-size: 0.85rem;
            color: #666;
            margin-top: 15px;
        }

        .switch-form a {
            color: var(--accent-salmon);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .switch-form a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .modal-content {
                max-width: 95%;
                padding: 0;
            }
            
            .modal-header {
                padding: 20px 20px 12px;
            }
            
            .modal-body {
                padding: 15px 20px;
            }
            
            .modal-header h2 {
                font-size: 1.3rem;
            }
            
            .input-group input {
                padding: 9px 11px;
                font-size: 0.9rem;
            }
            
            .btn-submit {
                padding: 11px;
                font-size: 0.95rem;
            }
        }

        /* Loading Spinner */
        .ph-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Success Message */
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #4caf50;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>

    <header id="navbar">
        <div class="logo">MOOBIX THEATER</div>
        <?php if(!$isAdmin): ?>
        <nav class="main-nav">
            <a href="#hero-section">NEWEST HIT</a>
            <a href="#schedule-section">MORE FILMS</a>
        </nav>
        <?php endif; ?>
        <div class="login-area">
            <?php if(!$isAdmin): ?>
            <div class="search-box"><input type="text" id="searchInput" placeholder="Search film..."></div>
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
                                <span class="user-role-badge"><?php echo $isAdmin ? 'Administrator' : 'Regular User'; ?></span>
                            </div>
                        </div>
                        
                        <div class="divider-mini"></div>
                        
                        <?php if($isAdmin): ?>
                            <a href="ui_index.php"><i class="ph ph-gear"></i> Admin Panel</a>
                            <a href="user_manage.php"><i class="ph ph-users"></i> Manage Users</a>
                            <a href="manage_bookings.php"><i class="ph ph-calendar-check"></i> Bookings</a>
                            
                            <div class="divider-mini"></div>
                            
                        <?php else: ?>
                            <a href="index.php" class="active"><i class="ph ph-house"></i> Home/Beranda</a>
                            <a href="my_tickets.php"><i class="ph ph-ticket"></i> Tiket Saya</a>
                            <a href="transaction_history.php"><i class="ph ph-clock-counter-clockwise"></i> Riwayat Transaksi</a>
                            <a href="edit_profile.php"><i class="ph ph-pencil-simple"></i> Edit Profil</a>
                        <?php endif; ?>
                        
                        <div class="divider-mini"></div>
                        <a href="?logout=true" class="logout-btn"><i class="ph ph-sign-out"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <button class="login-btn" onclick="showModal('loginModal')">LOGIN</button>
            <?php endif; ?>
        </div>
    </header>

    <?php
    // Load appropriate dashboard based on user role
    if ($isAdmin) {
        include 'admin_dashboard.php';
    } else {
        include 'user_dashboard.php';
    }
    ?>

    <!-- Login Modal -->
    <?php if(!$isLoggedIn): ?>
    <div class="modal-overlay" id="loginModal">
        <div class="modal-content">
            <span class="close-modal" onclick="hideModal('loginModal')">&times;</span>
            
            <div class="modal-header">
                <div class="modal-icon modal-login-icon">
                    <i class="ph ph-ticket"></i>
                </div>
                <h2>WELCOME BACK</h2>
                <p>Sign in to your Moobix account</p>
            </div>
            
            <div class="modal-body">
                <?php if($login_error): ?>
                <div class="error-message">
                    <i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($login_error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <input type="hidden" name="login_form" value="1">
                    
                    <div class="input-group">
                        <label for="username">
                            <i class="ph ph-user" style="margin-right: 5px;"></i> Username
                        </label>
                        <input type="text" 
                               name="username" 
                               id="username" 
                               required 
                               placeholder="Enter your username"
                               autocomplete="username"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    
                    <div class="input-group">
                        <label for="password">
                            <i class="ph ph-lock" style="margin-right: 5px;"></i> Password
                        </label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   required 
                                   placeholder="Enter your password"
                                   autocomplete="current-password">
                            <button type="button" 
                                    class="password-toggle" 
                                    onclick="togglePassword('password', this)">
                                <i class="ph ph-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit btn-login" id="loginSubmitBtn">
                        <i class="ph ph-sign-in"></i> ENTER & LOGIN
                    </button>
                    
                    <div class="modal-divider">
                        <span>OR</span>
                    </div>
                    
                    <button type="button" 
                            class="btn-submit" 
                            style="background: linear-gradient(135deg, #4CAF50, #2E7D32);"
                            onclick="hideModal('loginModal'); showModal('registerModal')">
                        <i class="ph ph-user-plus"></i> CREATE NEW ACCOUNT
                    </button>
                    
                    <div class="switch-form">
                        Don't have an account? 
                        <a onclick="hideModal('loginModal'); showModal('registerModal')">Sign up here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Register Modal -->
    <?php if(!$isLoggedIn): ?>
    <div class="modal-overlay" id="registerModal">
        <div class="modal-content">
            <span class="close-modal" onclick="hideModal('registerModal')">&times;</span>
            
            <div class="modal-header">
                <div class="modal-icon modal-register-icon">
                    <i class="ph ph-user-plus"></i>
                </div>
                <h2>CREATE ACCOUNT</h2>
                <p>Join Moobix for exclusive benefits</p>
            </div>
            
            <div class="modal-body">
                <?php if($register_error): ?>
                <div class="error-message">
                    <i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($register_error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" id="registerForm">
                    <input type="hidden" name="register_form" value="1">
                    
                    <div class="input-group">
                        <label for="fullname">
                            <i class="ph ph-user-circle" style="margin-right: 5px;"></i> Full Name
                        </label>
                        <input type="text" 
                               name="fullname" 
                               id="fullname" 
                               required 
                               placeholder="Enter your full name"
                               autocomplete="name"
                               value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
                    </div>
                    
                    <div class="input-group">
                        <label for="email">
                            <i class="ph ph-envelope" style="margin-right: 5px;"></i> Email Address
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               required 
                               placeholder="your.email@example.com"
                               autocomplete="email"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="input-group">
                        <label for="reg_username">
                            <i class="ph ph-user" style="margin-right: 5px;"></i> Username
                        </label>
                        <input type="text" 
                               name="reg_username" 
                               id="reg_username" 
                               required 
                               placeholder="Choose a username"
                               autocomplete="username"
                               value="<?php echo isset($_POST['reg_username']) ? htmlspecialchars($_POST['reg_username']) : ''; ?>">
                    </div>
                    
                    <div class="input-group">
                        <label for="reg_password">
                            <i class="ph ph-lock" style="margin-right: 5px;"></i> Password
                        </label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   name="reg_password" 
                                   id="reg_password" 
                                   required 
                                   placeholder="Create a strong password"
                                   autocomplete="new-password">
                            <button type="button" 
                                    class="password-toggle" 
                                    onclick="togglePassword('reg_password', this)">
                                <i class="ph ph-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="confirm_password">
                            <i class="ph ph-lock-key" style="margin-right: 5px;"></i> Confirm Password
                        </label>
                        <input type="password" 
                               name="confirm_password" 
                               id="confirm_password" 
                               required 
                               placeholder="Confirm your password"
                               autocomplete="new-password">
                    </div>
                    
                    <div class="input-group">
                        <label for="phone">
                            <i class="ph ph-phone" style="margin-right: 5px;"></i> Phone Number (Optional)
                        </label>
                        <input type="tel" 
                               name="phone" 
                               id="phone" 
                               placeholder="+62 812 3456 7890"
                               autocomplete="tel"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="terms-checkbox">
                        <input type="checkbox" name="terms" id="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                        <span>
                            I agree to the 
                            <a href="#" style="color: var(--accent-salmon);">Terms of Service</a> 
                            and 
                            <a href="#" style="color: var(--accent-salmon);">Privacy Policy</a>
                        </span>
                    </div>
                    
                    <button type="submit" class="btn-submit btn-register" id="registerSubmitBtn">
                        <i class="ph ph-user-plus"></i> CREATE ACCOUNT
                    </button>
                    
                    <div class="modal-divider">
                        <span>OR</span>
                    </div>
                    
                    <button type="button" 
                            class="btn-alt"
                            onclick="hideModal('registerModal'); showModal('loginModal')">
                        <i class="ph ph-sign-in"></i> SIGN IN INSTEAD
                    </button>
                    
                    <div class="switch-form">
                        Already have an account? 
                        <a onclick="hideModal('registerModal'); showModal('loginModal')">Sign in here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Booking Modal (Common for both dashboards) -->
    <div class="modal-overlay" id="bookingModal">
        <div class="ticket-modal booking-modal-wide">
            <span class="close-modal" onclick="toggleModal('bookingModal', false)">&times;</span>
            <div id="step-info" class="booking-step">
                <div class="movie-info-layout">
                    <div class="info-poster">
                        <img id="modalPoster" src="" alt="Movie Poster" onerror="this.src='https://via.placeholder.com/300x450?text=No+Image'">
                    </div>
                    
                    <div class="info-text">
                        <h2 id="modalTitle" style="margin-top:0; font-size:2rem; line-height:1.1;">TITLE</h2>
                        
                        <div style="display:flex; align-items:center; gap:15px; margin:10px 0; color:#666;">
                            <span style="display:flex; align-items:center; gap:5px;">
                                <span id="modalDuration">2h 0m</span>
                            </span>
                            
                            <span class="rating-badge">
                                <i class="ph ph-star-fill" style="color: gold;"></i> 
                                <span id="modalRating">0.0</span> / 5.0
                            </span>
                        </div>

                        <p id="modalSynopsis" class="synopsis" style="color:#444; line-height:1.6;">Synopsis...</p>
                        
                        <button class="btn-primary" onclick="proceedToSchedule()" style="margin-top:15px;">
                            BUY TICKET
                        </button>
                    </div>
                </div>
            </div>
            <div id="step-schedule" class="booking-step" style="display:none;">
                <h2>SELECT SCHEDULE</h2>
                <div class="date-picker-wrapper">
                    <?php
                    // Generate tanggal untuk 7 hari ke depan
                    $dates = [];
                    for ($i = 0; $i < 7; $i++) {
                        $date = date('Y-m-d', strtotime("+$i days"));
                        $dayName = date('D', strtotime($date));
                        $dayNum = date('d', strtotime($date));
                        $monthName = date('M', strtotime($date));
                        $dates[] = [
                            'full' => $date,
                            'day' => $dayName,
                            'dayNum' => $dayNum,
                            'month' => $monthName
                        ];
                    }
                    foreach ($dates as $date): ?>
                    <div class="date-item" onclick="selectDate(this, '<?php echo $date['full']; ?>')">
                        <span><?php echo $date['month']; ?></span>
                        <b><?php echo $date['dayNum']; ?></b>
                        <small><?php echo $date['day']; ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div id="timeSlots" class="time-slots-wrapper" style="display:none;">
                    <p style="text-align:center;">AVAILABLE TIME:</p>
                    <div class="slots">
                        <button class="time-btn" onclick="selectTime(this, '10:00')">10:00</button>
                        <button class="time-btn" onclick="selectTime(this, '14:00')">14:00</button>
                        <button class="time-btn" onclick="selectTime(this, '19:30')">19:30</button>
                    </div>
                </div>
                <div id="seatMapArea" style="display:none;">
                    <div class="divider-mini" style="margin:20px 0;"></div>
                    <div class="qty-control">
                        <button class="qty-btn" onclick="updateQty(-1)">-</button>
                        <span><span id="qtyDisplay">1</span> Tiket</span>
                        <button class="qty-btn" onclick="updateQty(1)">+</button>
                    </div>
                    <div class="seat-container">
                        <div class="screen">SCREEN</div>
                        <div class="row"><div class="seat">A1</div><div class="seat">A2</div><div class="seat occupied">A3</div><div class="seat">A4</div><div class="seat">A5</div><div class="seat">A6</div></div>
                        <div class="row"><div class="seat">B1</div><div class="seat">B2</div><div class="seat">B3</div><div class="seat">B4</div><div class="seat">B5</div><div class="seat">B6</div></div>
                        <div class="row"><div class="seat">C1</div><div class="seat">C2</div><div class="seat">C3</div><div class="seat">C4</div><div class="seat occupied">C5</div><div class="seat occupied">C6</div></div>
                        <div class="row"><div class="seat">D1</div><div class="seat">D2</div><div class="seat">D3</div><div class="seat">D4</div><div class="seat">D5</div><div class="seat">D6</div></div>
                    </div>
                    <p class="text" style="text-align:center;">Total: <b>Rp <span id="totalPrice">0</span></b></p>
                    <button class="btn-primary" style="width:100%; margin-top:15px;" onclick="showConfirmation()">CONFIRM</button>
                </div>
            </div>
            <div id="step-confirm" class="booking-step" style="display:none;">
                <h2>CONFIRMATION</h2>
                <div class="confirm-box">
                    <h3 id="confMovie">Movie</h3>
                    <p>Date: <b id="confDate">-</b> &bull; Time: <b id="confTime">-</b></p>
                    <p>Seats: <b id="confSeats" style="color:var(--accent-salmon)">-</b></p>
                    <p>Total: <b>Rp <span id="confTotal">0</span></b></p>
                </div>
                <div style="display:flex; gap:10px;">
                    <button class="btn-cancel" onclick="backToSeats()">CANCEL</button>
                    <button class="btn-pay-now" onclick="processPayment()">PAY NOW</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Modal (Only loaded for admin users) -->
    <?php if($isAdmin): ?>
    <div class="modal-overlay" id="adminModal">
        <div class="ticket-modal" style="width: 600px; max-height: 80vh; overflow-y: auto;">
            <span class="close-modal" onclick="toggleModal('adminModal', false)">&times;</span>
            <div id="adminModalContent"></div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Data session dan konfigurasi dari PHP
        const PHP_DATA = {
            isLoggedIn: <?php echo $isLoggedIn ? 'true' : 'false'; ?>,
            userRole: '<?php echo $userRole; ?>',
            userName: '<?php echo addslashes($userName); ?>',
            isAdmin: <?php echo $isAdmin ? 'true' : 'false'; ?>,
            isUser: <?php echo $isUser ? 'true' : 'false'; ?>
        };
        
        // Modal Functions untuk login/register yang DIPERKECIL
        function showModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                
                // Auto focus pada input pertama
                setTimeout(() => {
                    const firstInput = modal.querySelector('input');
                    if (firstInput) firstInput.focus();
                }, 300);
            }
        }
        
        function hideModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        // Password toggle function
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                button.innerHTML = '<i class="ph ph-eye-slash"></i>';
            } else {
                input.type = 'password';
                button.innerHTML = '<i class="ph ph-eye"></i>';
            }
        }
        
        // Form submission handling
        document.addEventListener('DOMContentLoaded', function() {
            // Login form submission
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    const submitBtn = document.getElementById('loginSubmitBtn');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> LOGGING IN...';
                        submitBtn.disabled = true;
                    }
                });
            }
            
            // Register form submission
            const registerForm = document.getElementById('registerForm');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    const submitBtn = document.getElementById('registerSubmitBtn');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> CREATING ACCOUNT...';
                        submitBtn.disabled = true;
                    }
                });
            }
            
            // Close modal when clicking outside
            const modals = document.querySelectorAll('.modal-overlay');
            modals.forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideModal(this.id);
                    }
                });
            });
            
            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    modals.forEach(modal => {
                        if (modal.style.display === 'flex') {
                            hideModal(modal.id);
                        }
                    });
                }
            });
            
            // Auto open modal jika ada error
             <?php 
        // Check if there's an actual error message (not just empty string)
        $hasLoginError = !empty($login_error);
        $hasRegisterError = !empty($register_error);
        
        if(($hasLoginError || $hasRegisterError) && !$isLoggedIn): 
        ?>
        setTimeout(function() {
            <?php if($hasRegisterError): ?>
            showModal('registerModal');
            <?php else: ?>
            showModal('loginModal');
            <?php endif; ?>
        }, 500);
        <?php endif; ?>
            
            // Initialize jika sudah login
            if(PHP_DATA.isLoggedIn) {
                console.log('User sudah login sebagai:', PHP_DATA.userName);
                console.log('Role:', PHP_DATA.userRole);
            }
        });
    </script>
    
    <script src="ui_script.js"></script>

</body>
</html>