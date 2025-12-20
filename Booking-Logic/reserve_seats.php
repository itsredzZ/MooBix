<?php
session_start();
header('Content-Type: application/json');
require '../UI/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
    exit();
}

if (!$input || empty($input['seats']) || empty($input['movie_id'])) {
    echo json_encode(['success' => false, 'message' => 'Data pemesanan tidak lengkap.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$movie_id = $input['movie_id'];
$seats = $input['seats'];
$booking_code = 'TMP-' . time(); // Kode sementara sebelum bayar

try {
    $pdo->beginTransaction();

    $sqlTrx = "INSERT INTO transactions (user_id, movie_id, booking_code, show_date, show_time, total_price, payment_status, transaction_date) 
               VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
    $stmtTrx = $pdo->prepare($sqlTrx);
    $stmtTrx->execute([
        $user_id,
        $movie_id,
        $booking_code,
        $input['show_date'],
        $input['show_time'],
        $input['total_price']
    ]);

    $transaction_id = $pdo->lastInsertId();

    $sqlSeat = "INSERT INTO booked_seats (transaction_id, movie_id, show_date, show_time, seat_number) VALUES (?, ?, ?, ?, ?)";
    $stmtSeat = $pdo->prepare($sqlSeat);

    foreach ($seats as $seat) {
        $stmtSeat->execute([
            $transaction_id,
            $movie_id,
            $input['show_date'],
            $input['show_time'],
            $seat
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'transaction_id' => $transaction_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
