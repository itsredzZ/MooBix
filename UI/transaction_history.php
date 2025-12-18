<?php
// File: transaction_history.php
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
// AMBIL DATA TIKET YANG SUDAH LEWAT
// ==========================================
$user_id = $_SESSION['user_id'];
$transactions = [];

try {
    // Query untuk mengambil data tiket yang SUDAH LEWAT tanggal tayang
    $query = "SELECT 
                t.*,
                m.title,
                m.poster,
                m.genre,
                m.synopsis,
                m.duration,
                m.rating,
                m.price
              FROM transactions t
              LEFT JOIN movies m ON t.movie_id = m.id
              WHERE t.user_id = ? 
              AND CONCAT(t.show_date, ' ', t.show_time) < NOW()
              ORDER BY t.show_date DESC, t.show_time DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo "<div style='color:red; padding:20px; background:#ffe6e6; border:1px solid red; margin:20px;'>";
    echo "<h3>Database Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
    exit();
}

// ==========================================
// FUNGSI HELPER
// ==========================================
function safe($array, $key, $default = '-') {
    return isset($array[$key]) ? htmlspecialchars($array[$key]) : $default;
}

function getPoster($filename) {
    $local_path = 'uploads/';
    if (empty($filename)) return 'https://via.placeholder.com/400x600?text=No+Image';
    
    if (strpos($filename, 'http') === 0) {
        return $filename;
    } else {
        return $local_path . $filename;
    }
}

// Data user
$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole = $_SESSION['user_role'] ?? 'user';
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$isLoggedIn = isset($_SESSION['user_name']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - MooBix</title>
    
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

    <div class="history-container">
        <div class="page-header">
            <h1><i class="ph ph-clock-counter-clockwise"></i> Riwayat Transaksi</h1>
            <p>Histori tiket yang sudah ditonton</p>
        </div>
        
        <?php if (count($transactions) > 0): ?>
            <!-- STATISTIK -->
            <?php
            $total_spent = 0;
            $total_tickets = count($transactions);
            $total_movies = count(array_unique(array_column($transactions, 'movie_id')));
            
            foreach ($transactions as $t) {
                $total_spent += $t['total_price'];
            }
            ?>
            
            <div class="history-stats">
                <div class="history-stat-card">
                    <div class="history-stat-number"><?php echo $total_tickets; ?></div>
                    <div class="history-stat-label">Total Tiket Ditonton</div>
                </div>
                <div class="history-stat-card">
                    <div class="history-stat-number">Rp <?php echo number_format($total_spent, 0, ',', '.'); ?></div>
                    <div class="history-stat-label">Total Pengeluaran</div>
                </div>
                <div class="history-stat-card">
                    <div class="history-stat-number"><?php echo $total_movies; ?></div>
                    <div class="history-stat-label">Film Berbeda</div>
                </div>
            </div>
            
            <!-- FILTER CONTROLS -->
            <div class="filter-controls">
                <button class="filter-btn active" onclick="filterTransactions('all')">Semua</button>
                <button class="filter-btn" onclick="filterTransactions('this_month')">Bulan Ini</button>
                <button class="filter-btn" onclick="filterTransactions('last_month')">Bulan Lalu</button>
                <button class="filter-btn" onclick="filterTransactions('this_year')">Tahun Ini</button>
            </div>
            
            <!-- DAFTAR RIWAYAT -->
            <div class="history-list">
                <?php foreach ($transactions as $ticket): 
                    // Format tanggal Indonesia
                    $show_date = new DateTime($ticket['show_date']);
                    $transaction_date = new DateTime($ticket['transaction_date']);
                    $now = new DateTime();
                    
                    // Hitung selisih waktu
                    $interval = $now->diff($show_date);
                    $days_ago = $interval->days;
                    
                    if ($days_ago == 0) {
                        $time_ago = 'Hari ini';
                    } elseif ($days_ago == 1) {
                        $time_ago = 'Kemarin';
                    } elseif ($days_ago < 30) {
                        $time_ago = $days_ago . ' hari yang lalu';
                    } elseif ($days_ago < 365) {
                        $months_ago = floor($days_ago / 30);
                        $time_ago = $months_ago . ' bulan yang lalu';
                    } else {
                        $years_ago = floor($days_ago / 365);
                        $time_ago = $years_ago . ' tahun yang lalu';
                    }
                    
                    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    
                    // Format tanggal tayang
                    $hari_show = $hari[$show_date->format('w')];
                    $tanggal_show = $show_date->format('j');
                    $bulan_show = $bulan[$show_date->format('n')-1];
                    $tahun_show = $show_date->format('Y');
                    
                    // Format tanggal pembelian
                    $hari_trans = $hari[$transaction_date->format('w')];
                    $tanggal_trans = $transaction_date->format('j');
                    $bulan_trans = $bulan[$transaction_date->format('n')-1];
                    $tahun_trans = $transaction_date->format('Y');
                    $jam_trans = $transaction_date->format('H:i');
                    
                    // Format rating bintang
                    $rating = floatval($ticket['rating'] ?? 0);
                    $stars_full = floor($rating);
                    $stars_empty = 5 - $stars_full;
                    
                    // Tentukan path gambar poster
                    $poster_path = getPoster(safe($ticket, 'poster'));
                    
                    // Format harga
                    $total_price = number_format($ticket['total_price'], 0, ',', '.');
                ?>
                <div class="history-item" data-show-date="<?php echo $ticket['show_date']; ?>" data-status="<?php echo $ticket['payment_status']; ?>">
                    <div class="watched-badge">
                        <i class="ph ph-check-circle"></i> Sudah Ditonton
                    </div>
                    
                    <div class="history-item-header">
                        <div class="history-code"><?php echo htmlspecialchars($ticket['booking_code']); ?></div>
                        <div class="history-status <?php echo $ticket['payment_status']; ?>">
                        </div>
                    </div>
                    
                    <div class="history-content">
                        <img src="<?php echo $poster_path; ?>" 
                             alt="<?php echo htmlspecialchars($ticket['title'] ?? 'Film'); ?>" 
                             class="history-poster"
                             onerror="this.src='https://via.placeholder.com/400x600?text=No+Image'">
                        
                        <div class="history-details">
                            <h3 class="history-title"><?php echo htmlspecialchars($ticket['title'] ?? 'Film Tidak Ditemukan'); ?></h3>
                            
                            <div class="history-meta">
                                <?php if (!empty($ticket['genre'])): ?>
                                <div class="history-genre">
                                    <i class="ph ph-film-script"></i> <?php echo htmlspecialchars($ticket['genre']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($ticket['duration'])): ?>
                                <div class="history-duration">
                                    <i class="ph ph-clock"></i> <?php echo htmlspecialchars($ticket['duration']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($ticket['rating'])): ?>
                                <div class="history-rating">
                                    <span class="stars">
                                        <?php 
                                        for ($i = 0; $i < $stars_full; $i++) {
                                            echo '<i class="ph ph-star-fill"></i>';
                                        }
                                        for ($i = 0; $i < $stars_empty; $i++) {
                                            echo '<i class="ph ph-star"></i>';
                                        }
                                        ?>
                                    </span>
                                    <span><?php echo number_format($rating, 1); ?>/5.0</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="history-timeline">
                                <div class="timeline-item">
                                    <span class="timeline-label"><i class="ph ph-calendar-blank"></i> Tanggal Nonton</span>
                                    <span class="timeline-value"><?php echo "$hari_show, $tanggal_show $bulan_show $tahun_show"; ?></span>
                                    <span class="time-difference"><?php echo $time_ago; ?></span>
                                </div>
                                
                                <div class="timeline-item">
                                    <span class="timeline-label"><i class="ph ph-clock"></i> Jam Tayang</span>
                                    <span class="timeline-value"><?php echo htmlspecialchars($ticket['show_time']); ?> WIB</span>
                                </div>
                                
                                <div class="timeline-item">
                                    <span class="timeline-label"><i class="ph ph-shopping-cart"></i> Tanggal Beli</span>
                                    <span class="timeline-value"><?php echo "$hari_trans, $tanggal_trans $bulan_trans $tahun_trans"; ?></span>
                                </div>
                                
                                <div class="timeline-item">
                                    <span class="timeline-label"><i class="ph ph-currency-circle-dollar"></i> Total Bayar</span>
                                    <span class="timeline-value" style="color: var(--accent-salmon);">Rp <?php echo $total_price; ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($ticket['synopsis'])): ?>
                            <div class="timeline-item" style="grid-column: 1 / -1; margin-top: 15px;">
                                <span class="timeline-label"><i class="ph ph-file-text"></i> Sinopsis</span>
                                <span class="timeline-value" style="font-size: 0.95rem; line-height: 1.6; font-family: var(--font-body);">
                                    <?php echo htmlspecialchars(substr($ticket['synopsis'], 0, 200)) . '...'; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="history-actions">
                        <button class="history-btn btn-review" onclick="writeReview('<?php echo $ticket['id']; ?>', '<?php echo htmlspecialchars($ticket['title']); ?>')">
                            <i class="ph ph-pencil-line"></i> Tulis Review
                        </button>
                        <button class="history-btn btn-receipt" onclick="downloadReceipt('<?php echo $ticket['booking_code']; ?>')">
                            <i class="ph ph-receipt"></i> Download Struk
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- TAMPILAN JIKA TIDAK ADA RIWAYAT -->
            <div class="no-history">
                <div class="no-history-icon">
                    <i class="ph ph-clock-counter-clockwise"></i>
                </div>
                <h3>Belum Ada Riwayat Nonton</h3>
                <p>Anda belum memiliki riwayat menonton film. Pesan tiket dan nikmati pengalaman menonton di bioskop kami!</p>
                <a href="ui_index.php" class="btn-back">
                    <i class="ph ph-ticket"></i> Pesan Tiket Sekarang
                </a>
            </div>
        <?php endif; ?>
    </div>
    <script src="ui_script.js"></script>
</body>
</html>