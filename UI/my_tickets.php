<?php
session_start();
require 'db.php';

$isHomePage = false;

if (!isset($_SESSION['user_id'])) {
    header('Location: login_process.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$tickets = [];

try {
    $query = "SELECT 
                t.*,
                m.title,
                m.poster,
                m.genre,
                m.synopsis,
                m.duration,
                m.rating,
                m.price,
                (
                    SELECT GROUP_CONCAT(seat_number SEPARATOR ', ') 
                    FROM booked_seats 
                    WHERE transaction_id = t.id
                ) as seat_numbers
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
    echo "<div style='color:red; padding:20px;'>Database Error: " . $e->getMessage() . "</div>";
    exit();
}

function safe($array, $key, $default = '-')
{
    return isset($array[$key]) ? htmlspecialchars($array[$key]) : $default;
}

function getPoster($filename)
{
    $local_path = 'uploads/';
    if (empty($filename)) return 'https://via.placeholder.com/400x600?text=No+Image';
    if (strpos($filename, 'http') === 0) return $filename;
    return $local_path . $filename;
}

$userName = $_SESSION['user_name'] ?? '';
$userRole = $_SESSION['user_role'] ?? 'user';
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
        .ticket-content {
            position: relative;
        }

        .seat-numbers {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: #FFD700;
            padding: 8px 15px;
            border-radius: 8px;
            font-family: var(--font-head);
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        .qr-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .qr-modal-content {
            background: white;
            padding: 0;
            border-radius: 16px;
            position: relative;
            max-width: 320px;
            width: 100%;
            overflow: hidden;
        }

        .digital-ticket {
            text-align: center;
            padding-bottom: 20px;
        }

        .ticket-header-modal {
            background: #1a1a1a;
            color: #e50914;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e50914;
            font-weight: bold;
        }

        .ticket-body-modal {
            padding: 20px;
            text-align: left;
        }

        .ticket-body-modal h2 {
            margin: 0 0 10px 0;
            color: #000;
            font-size: 1.2rem;
        }

        .info-grid-modal {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .info-item-modal label {
            font-size: 0.7rem;
            color: #888;
            display: block;
        }

        .info-item-modal span {
            font-weight: bold;
            color: #000;
            font-size: 0.95rem;
        }

        .ticket-divider {
            height: 20px;
            border-top: 2px dashed #ccc;
            margin: 10px 0;
            position: relative;
        }

        .close-qr {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            z-index: 100;
        }

        #qrcode-container img {
            margin: 0 auto;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="tickets-container">
        <div class="page-header">
            <h1><i class="ph ph-ticket"></i> Tiket Saya</h1>
            <p>Tiket aktif yang akan datang</p>
        </div>

        <?php if (count($tickets) > 0): ?>
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

            <div class="tickets-list">
                <?php foreach ($tickets as $ticket):
                    $show_date = new DateTime($ticket['show_date']);
                    $transaction_date = new DateTime($ticket['transaction_date']);

                    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                    $hari_show = $hari[$show_date->format('w')];
                    $tanggal_show = $show_date->format('j');
                    $bulan_show = $bulan[$show_date->format('n') - 1];
                    $tahun_show = $show_date->format('Y');

                    $hari_trans = $hari[$transaction_date->format('w')];
                    $tanggal_trans = $transaction_date->format('j');
                    $bulan_trans = $bulan[$transaction_date->format('n') - 1];
                    $tahun_trans = $transaction_date->format('Y');
                    $jam_trans = $transaction_date->format('H:i');

                    $rating = floatval($ticket['rating'] ?? 0);
                    $stars_full = floor($rating);
                    $stars_empty = 5 - $stars_full;

                    $poster_path = getPoster(safe($ticket, 'poster'));
                    $total_price = number_format($ticket['total_price'], 0, ',', '.');
                    $movie_price = number_format($ticket['price'], 0, ',', '.');

                    $seat_numbers = !empty($ticket['seat_numbers']) ? $ticket['seat_numbers'] : '-';

                    $seats_array = explode(',', $seat_numbers);
                    $seats_display = implode(', ', array_slice($seats_array, 0, 3));
                    if (count($seats_array) > 3) $seats_display .= '...';
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
                                                for ($i = 0; $i < $stars_full; $i++) echo '<i class="ph ph-star-fill star-filled"></i>';
                                                for ($i = 0; $i < $stars_empty; $i++) echo '<i class="ph ph-star star-empty"></i>';
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
                                        <span class="detail-label"><i class="ph ph-shopping-cart"></i> Pembelian</span>
                                        <span class="detail-value"><?php echo "$tanggal_trans $bulan_trans - $jam_trans"; ?></span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label"><i class="ph ph-tag"></i> Harga</span>
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

                        <div class="ticket-action" style="margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 15px; text-align: center;">
                            <button class="btn-primary" type="button"
                                style="width: 100%; background: #e50914; color: white; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 8px;"
                                onclick="showQRCode(
                            '<?php echo $ticket['booking_code']; ?>',
                            '<?php echo htmlspecialchars($ticket['title'], ENT_QUOTES); ?>', 
                            '<?php echo "$tanggal_show $bulan_show"; ?>',
                            '<?php echo $ticket['show_time']; ?>',
                            '<?php echo safe($ticket, 'seat_numbers', '-'); ?>' 
                        )">
                                <i class="ph ph-qr-code" style="font-size: 1.2rem;"></i> LIHAT E-TICKET
                            </button>
                        </div>

                        <div class="ticket-footer">
                            <div><i class="ph ph-info"></i> Harap datang 30 menit sebelum jam tayang</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-tickets">
                <div class="no-tickets-icon"><i class="ph ph-ticket"></i></div>
                <h3>Belum Ada Tiket Aktif</h3>
                <p>Anda belum memiliki tiket yang sedang aktif. Silakan kembali ke halaman utama.</p>
                <a href="ui_index.php" class="btn-browse"><i class="ph ph-house"></i> Kembali ke Beranda</a>
            </div>
        <?php endif; ?>
    </div>

    <div id="qrModal" class="qr-modal-overlay">
        <div class="qr-modal-content">
            <span class="close-qr" onclick="closeQRModal()">&times;</span>
            <div class="digital-ticket">
                <div class="ticket-header-modal">
                    <span>MOOBIX</span>
                </div>
                <div class="ticket-body-modal">
                    <h2 id="modal-movie-title">JUDUL FILM</h2>
                    <div class="info-grid-modal">
                        <div class="info-item-modal"><label>DATE</label><span id="modal-date">-</span></div>
                        <div class="info-item-modal"><label>TIME</label><span id="modal-time">-</span></div>
                        <div class="info-item-modal"><label>SEATS</label><span id="modal-seats" style="color:#e50914">-</span></div>
                    </div>
                    <div class="ticket-divider"></div>
                    <div style="text-align: center;">
                        <p style="font-size:0.7rem; color:#888; margin-bottom:10px;">SCAN THIS CODE AT ENTRANCE</p>
                        <div id="qrcode-container" style="margin-bottom: 10px;"></div>
                        <h3 id="qr-booking-code" style="letter-spacing: 2px; margin:0; color:#e50914">CODE</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="ui_script.js"></script>

    <script>
        let qrcodeObj = null;

        function showQRCode(code, title, date, time, seats) {
            const modal = document.getElementById('qrModal');
            if (!modal) return;

            modal.style.display = 'flex';

            document.getElementById('modal-movie-title').innerText = title;
            document.getElementById('modal-date').innerText = date;

            let cleanTime = time.length > 5 ? time.substring(0, 5) : time;
            document.getElementById('modal-time').innerText = cleanTime;

            document.getElementById('modal-seats').innerText = seats;
            document.getElementById('qr-booking-code').innerText = code;

            const container = document.getElementById('qrcode-container');
            container.innerHTML = "";

            setTimeout(() => {
                qrcodeObj = new QRCode(container, {
                    text: code,
                    width: 140,
                    height: 140,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            }, 50);
        }

        function closeQRModal() {
            document.getElementById('qrModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('qrModal');
            if (event.target == modal) {
                closeQRModal();
            }
        }
    </script>
</body>

</html>