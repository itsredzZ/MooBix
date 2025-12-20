<?php
require '../UI/db.php';

$movie_id = $_GET['movie_id'];
$date = $_GET['date'];
$time = $_GET['time'];

// Hapus transaksi pending yang sudah lebih dari 10 menit
$pdo->query("DELETE FROM transactions 
             WHERE payment_status = 'pending' 
             AND transaction_date < (NOW() - INTERVAL 10 MINUTE)");

$stmt = $pdo->prepare("
    SELECT bs.seat_number 
    FROM booked_seats bs
    JOIN transactions t ON bs.transaction_id = t.id
    WHERE bs.movie_id = ? AND bs.show_date = ? AND bs.show_time = ?
    AND t.payment_status IN ('paid', 'pending')
");
$stmt->execute([$movie_id, $date, $time]);
$seats = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($seats);
