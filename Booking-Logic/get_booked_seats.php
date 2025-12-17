<?php
require '../UI/db.php';
$movie_id = $_GET['movie_id'];
$date = $_GET['date'];
$time = $_GET['time'];

$stmt = $pdo->prepare("SELECT seat_number FROM booked_seats WHERE movie_id = ? AND show_date = ? AND show_time = ?");
$stmt->execute([$movie_id, $date, $time]);
$seats = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($seats);
?>