<?php
// File: my_tickets.php
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
// AMBIL DATA TIKET AKTIF DARI DATABASE
// ==========================================
$user_id = $_SESSION['user_id'];
$tickets = [];

try {
    // Query untuk mengambil data tiket aktif
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
              AND t.payment_status = 'paid'
              AND (CONCAT(t.show_date, ' ', t.show_time) > NOW())
              ORDER BY t.show_date ASC, t.show_time ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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

// Data user untuk header
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
    <title>Tiket Saya - MooBix</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Dancing+Script:wght@700&family=Oswald:wght@300;400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <link rel="stylesheet" href="ui_style.css">
    <style>
        /* Additional custom styles if needed */
        .ticket-content {
            position: relative;
        }
        
        .seat-numbers {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            color: #FFD700;
            padding: 8px 15px;
            border-radius: 8px;
            font-family: var(--font-head);
            letter-spacing: 1px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <!-- ==========================================
        INCLUDE UNIVERSAL NAVBAR
        (Otomatis akan render navbar minimalis karena $isHomePage = false)
    ========================================== -->
    <?php include 'navbar.php'; ?>

    <div class="tickets-container">
        <div class="page-header">
            <h1><i class="ph ph-ticket"></i> Tiket Saya</h1>
            <p>Tiket aktif yang akan datang</p>
        </div>
        
        <?php if (count($tickets) > 0): ?>
            <!-- STATISTIK TIKET -->
            <?php
            $total_tickets = count($tickets);
            $total_spent = 0;
            $upcoming_count = 0;
            $today = date('Y-m-d');
            
            foreach ($tickets as $ticket) {
                $total_spent += $ticket['total_price'];
                if ($ticket['show_date'] >= $today) {
                    $upcoming_count++;
                }
            }
            ?>
            
            <div class="ticket-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_tickets; ?></div>
                    <div class="stat-label">Total Tiket</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">Rp <?php echo number_format($total_spent, 0, ',', '.'); ?></div>
                    <div class="stat-label">Total Pembelian</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $upcoming_count; ?></div>
                    <div class="stat-label">Akan Datang</div>
                </div>
            </div>
            
            <!-- DAFTAR TIKET -->
            <div class="tickets-list">
                <?php foreach ($tickets as $ticket): 
                    // Format tanggal Indonesia
                    $show_date = new DateTime($ticket['show_date']);
                    $transaction_date = new DateTime($ticket['transaction_date']);
                    
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
                    $movie_price = number_format($ticket['price'], 0, ',', '.');
                    
                    // Format seat numbers
                    $seat_numbers = isset($ticket['seat_numbers']) ? $ticket['seat_numbers'] : 'A1,A2';
                    $seats_array = explode(',', $seat_numbers);
                    $seats_display = implode(', ', array_slice($seats_array, 0, 3));
                    if (count($seats_array) > 3) {
                        $seats_display .= '...';
                    }
                ?>
                <div class="ticket-item" data-ticket-id="<?php echo $ticket['id']; ?>">
                    <div class="ticket-header">
                        <div class="ticket-code"><?php echo htmlspecialchars($ticket['booking_code']); ?></div>
                        <div class="ticket-status status-paid">
                            <i class="ph ph-check-circle"></i> Lunas
                        </div>
                    </div>
                    
                    <div class="ticket-content">
                        <div class="seat-numbers" title="Kursi: <?php echo htmlspecialchars($seat_numbers); ?>">
                            <i class="ph ph-armchair"></i> <?php echo htmlspecialchars($seats_display); ?>
                        </div>
                        
                        <img src="<?php echo $poster_path; ?>" 
                             alt="<?php echo htmlspecialchars($ticket['title'] ?? 'Film'); ?>" 
                             class="ticket-poster"
                             onerror="this.src='https://via.placeholder.com/400x600?text=No+Image'">
                        
                        <div class="ticket-details">
                            <h3 class="movie-title"><?php echo htmlspecialchars($ticket['title'] ?? 'Film Tidak Ditemukan'); ?></h3>
                            
                            <div class="movie-meta">
                                <?php if (!empty($ticket['genre'])): ?>
                                <div class="movie-genre">
                                    <i class="ph ph-film-script"></i> <?php echo htmlspecialchars($ticket['genre']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($ticket['duration'])): ?>
                                <div class="movie-duration">
                                    <i class="ph ph-clock"></i> <?php echo htmlspecialchars($ticket['duration']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($ticket['rating'])): ?>
                                <div class="movie-rating">
                                    <span class="stars">
                                        <?php 
                                        // Tampilkan bintang penuh
                                        for ($i = 0; $i < $stars_full; $i++) {
                                            echo '<i class="ph ph-star-fill star-filled"></i>';
                                        }
                                        // Tampilkan bintang kosong
                                        for ($i = 0; $i < $stars_empty; $i++) {
                                            echo '<i class="ph ph-star star-empty"></i>';
                                        }
                                        ?>
                                    </span>
                                    <span><?php echo number_format($rating, 1); ?>/5.0</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label"><i class="ph ph-calendar-blank"></i> Tanggal Tayang</span>
                                    <span class="detail-value"><?php echo "$hari_show, $tanggal_show $bulan_show $tahun_show"; ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label"><i class="ph ph-clock"></i> Jam Tayang</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($ticket['show_time']); ?> WIB</span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label"><i class="ph ph-shopping-cart"></i> Tanggal Pembelian</span>
                                    <span class="detail-value"><?php echo "$hari_trans, $tanggal_trans $bulan_trans $tahun_trans - $jam_trans"; ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <span class="detail-label"><i class="ph ph-tag"></i> Harga Tiket</span>
                                    <span class="detail-value">Rp <?php echo $movie_price; ?>/tiket</span>
                                </div>
                            </div>
                            
                            <?php if (!empty($ticket['synopsis'])): ?>
                            <div class="detail-item" style="grid-column: 1 / -1; margin-top: 15px;">
                                <span class="detail-label"><i class="ph ph-file-text"></i> Sinopsis</span>
                                <span class="detail-value" style="font-size: 0.95rem; line-height: 1.6; font-family: var(--font-body);">
                                    <?php echo htmlspecialchars(substr($ticket['synopsis'], 0, 200)) . '...'; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="price-display">
                        <div class="price-label">Total Pembayaran</div>
                        <div class="price">Rp <?php echo $total_price; ?></div>
                    </div>
                    
                    <div class="ticket-actions">
                        <button class="btn-action btn-download" onclick="downloadTicket('<?php echo $ticket['booking_code']; ?>')">
                            <i class="ph ph-download"></i> Download E-Ticket
                        </button>
                        <button class="btn-action btn-print" onclick="printTicket()">
                            <i class="ph ph-printer"></i> Cetak Tiket
                        </button>
                        <button class="btn-action btn-cancel" onclick="cancelTicket('<?php echo $ticket['id']; ?>', '<?php echo htmlspecialchars($ticket['title']); ?>')">
                            <i class="ph ph-x"></i> Batalkan Tiket
                        </button>
                    </div>
                    
                    <div class="ticket-footer">
                        <div>
                            <i class="ph ph-info"></i> 
                            Harap datang 30 menit sebelum jam tayang
                        </div>
                        <div>
                            ID Transaksi: <strong><?php echo $ticket['id']; ?></strong>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- TAMPILAN JIKA TIDAK ADA TIKET -->
            <div class="no-tickets">
                <div class="no-tickets-icon">
                    <i class="ph ph-ticket"></i>
                </div>
                <h3>Belum Ada Tiket Aktif</h3>
                <p>Anda belum memiliki tiket yang sedang aktif. Silakan kembali ke halaman utama untuk memesan tiket film favorit Anda.</p>
                <a href="ui_index.php" class="btn-browse">
                    <i class="ph ph-house"></i> Kembali ke Beranda
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- ==========================================
        INCLUDE UNIVERSAL FOOTER
    ========================================== -->
    <?php include 'footer.php'; ?>
    
    <!-- ==========================================
        INCLUDE JAVASCRIPT DARI ui_script.js
    ========================================== -->
    <script src="ui_script.js"></script>
    
    <script>
        // Additional JavaScript for this page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('My Tickets page loaded');
        });
    </script>
</body>
</html>