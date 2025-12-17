<?php
// File: manage_bookings.php
session_start();
require 'db.php';

// ==========================================
// PROTEKSI HALAMAN ADMIN
// ==========================================
$isHomePage = false; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ui_index.php');
    exit();
}

// ==========================================
// FILTER PARAMETER
// ==========================================
$filterStatus = $_GET['status'] ?? 'all';
$filterDate = $_GET['date'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// ==========================================
// AMBIL SEMUA DATA BOOKING DENGAN FILTER
// ==========================================
$bookings = [];
$totalRevenue = 0;
$todayRevenue = 0;
$today = date('Y-m-d');

try {
    // Query dasar
    $query = "SELECT 
                t.*, 
                m.title as movie_title, 
                m.poster,
                m.genre,
                u.name as customer_name, 
                u.email as customer_email,
                (
                    SELECT GROUP_CONCAT(seat_number SEPARATOR ', ') 
                    FROM booked_seats 
                    WHERE transaction_id = t.id
                ) as seat_numbers
              FROM transactions t
              JOIN movies m ON t.movie_id = m.id
              JOIN users u ON t.user_id = u.id
              WHERE 1=1";

    // Filter berdasarkan status
    if ($filterStatus !== 'all') {
        $query .= " AND t.payment_status = :status";
    }
    
    // Filter berdasarkan tanggal
    if (!empty($filterDate)) {
        $query .= " AND DATE(t.transaction_date) = :filterDate";
    }
    
    // Filter berdasarkan pencarian
    if (!empty($searchQuery)) {
        $query .= " AND (m.title LIKE :search OR u.name LIKE :search OR u.email LIKE :search OR t.booking_code LIKE :search)";
    }
    
    $query .= " ORDER BY t.transaction_date DESC";
    
    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    if ($filterStatus !== 'all') {
        $stmt->bindValue(':status', $filterStatus);
    }
    if (!empty($filterDate)) {
        $stmt->bindValue(':filterDate', $filterDate);
    }
    if (!empty($searchQuery)) {
        $stmt->bindValue(':search', "%$searchQuery%");
    }
    
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Hitung statistik
    foreach ($bookings as $booking) {
        $totalRevenue += $booking['total_price'];
        if (date('Y-m-d', strtotime($booking['transaction_date'])) === $today) {
            $todayRevenue += $booking['total_price'];
        }
    }
    
    // Statistik berdasarkan status
    $statusStats = [];
    if ($bookings) {
        $statuses = ['pending', 'paid', 'cancelled', 'expired'];
        foreach ($statuses as $status) {
            $statusStats[$status] = count(array_filter($bookings, function($b) use ($status) {
                return $b['payment_status'] === $status;
            }));
        }
    }
    
} catch (PDOException $e) {
    echo "<div style='color:red; padding:20px;'>Database Error: " . $e->getMessage() . "</div>";
    exit();
}

function getPoster($filename) {
    $local_path = 'uploads/';
    if (empty($filename)) return 'https://via.placeholder.com/400x600?text=No+Image';
    return (strpos($filename, 'http') === 0) ? $filename : $local_path . $filename;
}

function getStatusColor($status) {
    $colors = [
        'paid' => '#2ecc71',
        'pending' => '#f39c12',
        'cancelled' => '#e74c3c',
        'expired' => '#95a5a6'
    ];
    return $colors[$status] ?? '#3498db';
}

function getStatusBg($status) {
    $colors = [
        'paid' => 'rgba(46, 204, 113, 0.15)',
        'pending' => 'rgba(243, 156, 18, 0.15)',
        'cancelled' => 'rgba(231, 76, 60, 0.15)',
        'expired' => 'rgba(149, 165, 166, 0.15)'
    ];
    return $colors[$status] ?? 'rgba(52, 152, 219, 0.15)';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Panel | MOOBIX THEATER</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="ui_style.css">
    
    <style>
        /* ===== ADMIN DASHBOARD STYLE ===== */
        .admin-container { 
            padding: 40px 5%; 
            max-width: 1400px; 
            margin: 0 auto;
            animation: fadeIn 0.5s ease;
        }
        
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(229, 9, 20, 0.2);
        }
        
        .page-header h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.8rem;
            color: #FFD700;
            letter-spacing: 1px;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .page-header p {
            color: #aaa;
            font-size: 1rem;
            margin: 0;
        }
        
        /* ===== STATISTICS CARDS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(30, 30, 40, 0.8), rgba(20, 20, 30, 0.9));
            border-radius: 16px;
            padding: 25px;
            border: 1px solid rgba(255, 215, 0, 0.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 215, 0, 0.3);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
        }
        
        .stat-number {
            font-family: 'Oswald', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #FFD700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #aaa;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* ===== STATUS FILTER ===== */
        .status-filter {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
        }
        
        .filter-btn {
            padding: 10px 25px;
            border-radius: 25px;
            border: 2px solid transparent;
            background: rgba(255, 255, 255, 0.08);
            color: #ddd;
            font-family: 'Oswald', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .filter-btn.active {
            background: #e50914;
            color: white;
            border-color: #e50914;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* ===== SEARCH AND FILTER BAR ===== */
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
        }
        
        .search-box input {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            font-family: 'Oswald', sans-serif;
            font-size: 1rem;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #e50914;
            background: rgba(255, 255, 255, 0.12);
        }
        
        .date-filter input {
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            font-family: 'Oswald', sans-serif;
            font-size: 1rem;
            min-width: 200px;
        }
        
        /* ===== BOOKING CARDS ===== */
        .booking-card {
            background: linear-gradient(135deg, rgba(40, 40, 50, 0.7), rgba(30, 30, 40, 0.9));
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 5px solid;
            display: grid;
            grid-template-columns: 100px 1fr 250px;
            gap: 25px;
            align-items: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .booking-card:hover {
            transform: translateX(5px);
            border-color: rgba(255, 215, 0, 0.2);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .poster-mini {
            width: 100px;
            height: 140px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        }
        
        .booking-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .movie-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.8rem;
            color: #FFD700;
            letter-spacing: 0.5px;
            margin: 0;
        }
        
        .customer-info h4 {
            color: #FFD700;
            margin: 0 0 5px 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .customer-info p {
            margin: 0;
            font-size: 0.85rem;
            color: #ccc;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #aaa;
            font-size: 0.9rem;
        }
        
        .detail-item i {
            color: #e50914;
        }
        
        .seat-badge {
            background: rgba(229, 9, 20, 0.2);
            color: #FFD700;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            border: 1px solid rgba(229, 9, 20, 0.3);
        }
        
        /* ===== RIGHT SIDE STATUS AND ACTIONS ===== */
        .status-area {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
        }
        
        .price-tag {
            font-family: 'Oswald', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #FFD700;
            text-align: center;
        }
        
        .booking-code {
            font-size: 0.8rem;
            color: #888;
            font-family: monospace;
            letter-spacing: 1px;
        }
        
        /* ===== ACTION BUTTONS ===== */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .action-btn {
            padding: 8px 15px;
            border-radius: 8px;
            border: none;
            font-family: 'Oswald', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .action-view {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
            border: 1px solid rgba(52, 152, 219, 0.3);
        }
        
        .action-view:hover {
            background: rgba(52, 152, 219, 0.3);
        }
        
        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            opacity: 0.5;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #e50914;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #aaa;
            font-size: 1.2rem;
            margin: 0;
        }
        
        /* ===== ANIMATIONS ===== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 992px) {
            .booking-card {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .poster-mini {
                width: 120px;
                height: 170px;
                margin: 0 auto;
            }
            
            .status-area {
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                padding-top: 20px;
                flex-direction: row;
                justify-content: space-between;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
            
            .filter-bar {
                flex-direction: column;
            }
            
            .search-box {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="admin-container">
        <div class="page-header">
            <h1><i class="ph ph-ticket"></i> Manage Bookings</h1>
            <p>Kelola dan pantau seluruh transaksi pemesanan tiket</p>
        </div>

        <!-- STATISTICS SECTION -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($bookings); ?></div>
                <div class="stat-label">Total Transaksi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?>
                </div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    Rp <?php echo number_format($todayRevenue, 0, ',', '.'); ?>
                </div>
                <div class="stat-label">Pendapatan Hari Ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php echo $statusStats['paid'] ?? 0; ?>
                </div>
                <div class="stat-label">Tiket Terbayar</div>
            </div>
        </div>

        <!-- STATUS FILTER -->
        <div class="status-filter">
            <a href="?status=all" class="filter-btn <?php echo $filterStatus === 'all' ? 'active' : ''; ?>">
                <i class="ph ph-list"></i> All Bookings (<?php echo count($bookings); ?>)
            </a>
            <a href="?status=paid" class="filter-btn <?php echo $filterStatus === 'paid' ? 'active' : ''; ?>">
                <span class="status-badge" style="background: <?php echo getStatusBg('paid'); ?>; color: <?php echo getStatusColor('paid'); ?>;">Paid</span>
                (<?php echo $statusStats['paid'] ?? 0; ?>)
            </a>
            <a href="?status=pending" class="filter-btn <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>">
                <span class="status-badge" style="background: <?php echo getStatusBg('pending'); ?>; color: <?php echo getStatusColor('pending'); ?>;">Pending</span>
                (<?php echo $statusStats['pending'] ?? 0; ?>)
            </a>
            <a href="?status=cancelled" class="filter-btn <?php echo $filterStatus === 'cancelled' ? 'active' : ''; ?>">
                <span class="status-badge" style="background: <?php echo getStatusBg('cancelled'); ?>; color: <?php echo getStatusColor('cancelled'); ?>;">Cancelled</span>
                (<?php echo $statusStats['cancelled'] ?? 0; ?>)
            </a>
        </div>

        <!-- SEARCH AND FILTER BAR -->
        <form method="GET" class="filter-bar">
            <div class="search-box">
                <input type="text" name="search" placeholder="Cari berdasarkan nama, email, judul film, atau kode booking..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
            </div>
            <div class="date-filter">
                <input type="date" name="date" value="<?php echo htmlspecialchars($filterDate); ?>">
            </div>
            <button type="submit" class="filter-btn active" style="background: #e50914; color: white;">
                <i class="ph ph-magnifying-glass"></i> Filter
            </button>
            <a href="manage_bookings.php" class="filter-btn">
                <i class="ph ph-arrow-clockwise"></i> Reset
            </a>
        </form>

        <!-- BOOKINGS LIST -->
        <div class="bookings-list">
            <?php if (count($bookings) > 0): ?>
                <?php foreach ($bookings as $b): 
                    $statusColor = getStatusColor($b['payment_status']);
                    $statusBg = getStatusBg($b['payment_status']);
                ?>
                    <div class="booking-card" style="border-left-color: <?php echo $statusColor; ?>;">
                        <!-- Poster Film -->
                        <img src="<?php echo getPoster($b['poster']); ?>" class="poster-mini" alt="<?php echo htmlspecialchars($b['movie_title']); ?>">

                        <!-- Detail Booking -->
                        <div class="booking-details">
                            <div class="customer-info">
                                <h4><i class="ph ph-user-circle"></i> <?php echo htmlspecialchars($b['customer_name']); ?></h4>
                                <p><i class="ph ph-envelope"></i> <?php echo htmlspecialchars($b['customer_email']); ?></p>
                                <p><i class="ph ph-identification-card"></i> ID Transaksi: #<?php echo $b['id']; ?></p>
                            </div>
                            
                            <h3 class="movie-title"><?php echo htmlspecialchars($b['movie_title']); ?></h3>
                            
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <i class="ph ph-calendar"></i>
                                    <span><?php echo date('d M Y', strtotime($b['show_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="ph ph-clock"></i>
                                    <span><?php echo substr($b['show_time'], 0, 5); ?> WIB</span>
                                </div>
                                <div class="detail-item">
                                    <i class="ph ph-ticket"></i>
                                    <span><?php echo $b['ticket_quantity']; ?> Tiket</span>
                                </div>
                                <div class="detail-item">
                                    <i class="ph ph-armchair"></i>
                                    <span class="seat-badge"><?php echo $b['seat_numbers'] ?: '-'; ?></span>
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <button class="action-btn action-view">
                                    <i class="ph ph-eye"></i> View Details
                                </button>
                                <button class="action-btn action-view" onclick="window.print()">
                                    <i class="ph ph-printer"></i> Print Ticket
                                </button>
                            </div>
                        </div>

                        <!-- Status dan Harga -->
                        <div class="status-area">
                            <div class="status-badge" style="background: <?php echo $statusBg; ?>; color: <?php echo $statusColor; ?>; border: 1px solid <?php echo $statusColor; ?>;">
                                <?php echo strtoupper($b['payment_status']); ?>
                            </div>
                            
                            <div class="price-tag">
                                Rp <?php echo number_format($b['total_price'], 0, ',', '.'); ?>
                            </div>
                            
                            <div class="booking-code">
                                <i class="ph ph-barcode"></i> <?php echo $b['booking_code']; ?>
                            </div>
                            
                            <div style="font-size: 0.75rem; color: #888;">
                                <?php echo date('d M Y H:i', strtotime($b['transaction_date'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="ph ph-ticket"></i>
                    <p>Belum ada data booking yang ditemukan.</p>
                    <?php if ($filterStatus !== 'all' || !empty($searchQuery) || !empty($filterDate)): ?>
                        <p style="margin-top: 10px; font-size: 0.9rem;">
                            <a href="manage_bookings.php" style="color: #e50914; text-decoration: none;">
                                <i class="ph ph-arrow-clockwise"></i> Reset filter untuk melihat semua booking
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto refresh data setiap 30 detik
        setTimeout(function() {
            if (!document.hidden) {
                location.reload();
            }
        }, 30000);

        // Live search on keyup
        document.querySelector('input[name="search"]').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });

        // Date filter auto-submit
        document.querySelector('input[name="date"]').addEventListener('change', function() {
            this.form.submit();
        });

        // Add hover effect to booking cards
        document.querySelectorAll('.booking-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
    </script>
</body>
</html>