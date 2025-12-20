<?php
session_start();
header('Content-Type: application/json');
require '../UI/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$transaction_id = $input['transaction_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$transaction_id) {
    echo json_encode(['success' => false, 'message' => 'ID Transaksi hilang.']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ? AND payment_status = 'pending'");
    $stmt->execute([$transaction_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Waktu booking habis atau transaksi tidak ditemukan.']);
        exit();
    }

    $final_booking_code = 'BKG-' . strtoupper(substr(md5(uniqid()), 0, 8));

    $sql = "UPDATE transactions SET 
            payment_status = 'paid', 
            booking_code = ?, 
            transaction_date = NOW() 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$final_booking_code, $transaction_id]);

    echo json_encode([
        'success' => true, 
        'booking_code' => $final_booking_code
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}