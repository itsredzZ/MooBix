<?php
// File: process_booking.php

// 1. Matikan error display agar tidak merusak JSON, tapi catat errornya
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// FUNGSI UNTUK KIRIM ERROR KE JS
function sendError($msg) {
    echo json_encode(['success' => false, 'message' => $msg]);
    exit();
}

// 2. CEK PATH DATABASE (PENTING!)
// Coba panggil db.php. Jika gagal, script akan lapor ke JS, bukan diam saja.
if (file_exists('../UI/db.php')) {
    require '../UI/db.php';      // Jika file ini di folder Booking-Logic
} elseif (file_exists('db.php')) {
    require 'db.php';            // Jika file ini satu folder dengan db.php
} elseif (file_exists('../db.php')) {
    require '../db.php';         // Jika db.php ada di root
} else {
    sendError("File database (db.php) tidak ditemukan! Cek path require.");
}

// 3. Cek Login
if (!isset($_SESSION['user_id'])) {
    sendError('Sesi habis. Silakan login ulang.');
}

// 4. Ambil Data JSON
$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true);

if (!$input) {
    sendError('Data JSON tidak valid atau kosong.');
}

$user_id = $_SESSION['user_id'];
$movie_id = $input['movie_id'] ?? null;
$show_date = $input['show_date'] ?? null;
$show_time = $input['show_time'] ?? null;
$seats = $input['seats'] ?? []; 
$total_price = $input['total_price'] ?? 0;

// Validasi input
if (empty($movie_id) || empty($show_date) || empty($show_time) || empty($seats)) {
    sendError('Data booking tidak lengkap (Movie/Date/Time/Seats kosong).');
}

try {
    // Mulai Transaksi
    $pdo->beginTransaction();

    // 5. CEK KETERSEDIAAN KURSI (Double check)
    $placeholders = str_repeat('?,', count($seats) - 1) . '?';
    $sqlCheck = "SELECT seat_number FROM booked_seats 
                 WHERE movie_id = ? AND show_date = ? AND show_time = ? 
                 AND seat_number IN ($placeholders)";
    
    $params = array_merge([$movie_id, $show_date, $show_time], $seats);
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute($params);
    $existingSeats = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);

    if (count($existingSeats) > 0) {
        $pdo->rollBack();
        sendError('Kursi berikut sudah dipesan orang lain: ' . implode(', ', $existingSeats));
    }

    // 6. INSERT TRANSACTIONS
    $booking_code = 'BKG-' . strtoupper(substr(md5(uniqid()), 0, 8)); 
    
    $sqlTrx = "INSERT INTO transactions (user_id, movie_id, booking_code, show_date, show_time, total_price, payment_status, transaction_date) 
               VALUES (?, ?, ?, ?, ?, ?, 'paid', NOW())";
    
    // CATATAN: Saya tambah kolom transaction_date & NOW() kalau di DB kamu ada kolom itu.
    // Kalau error "Column not found", hapus transaction_date & NOW().
    
    $stmtTrx = $pdo->prepare($sqlTrx);
    $stmtTrx->execute([$user_id, $movie_id, $booking_code, $show_date, $show_time, $total_price]);
    
    $transaction_id = $pdo->lastInsertId();

    // 7. INSERT BOOKED_SEATS
    $sqlSeat = "INSERT INTO booked_seats (transaction_id, movie_id, show_date, show_time, seat_number) 
                VALUES (?, ?, ?, ?, ?)";
    $stmtSeat = $pdo->prepare($sqlSeat);

    foreach ($seats as $seat) {
        $stmtSeat->execute([$transaction_id, $movie_id, $show_date, $show_time, $seat]);
    }

    // Komit Transaksi
    $pdo->commit();

    echo json_encode(['success' => true, 'booking_code' => $booking_code]);

} catch (PDOException $e) {
    $pdo->rollBack();
    // Tampilkan pesan error Database yang spesifik
    sendError('Database Error: ' . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    sendError('System Error: ' . $e->getMessage());
}
?>