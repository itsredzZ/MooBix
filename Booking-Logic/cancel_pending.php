<?php
session_start();
header('Content-Type: application/json');
require '../UI/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$transaction_id = $input['transaction_id'] ?? null;

if ($transaction_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND payment_status = 'pending'");
        $stmt->execute([$transaction_id]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
