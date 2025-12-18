<?php
session_start();
require_once 'db.php';

// Define upload directory (relative to this file)
// Based on your ui_index.php, images seem to be in '../ui/uploads/'
$uploadDir = 'UI/uploads/'; 
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$response = ['status' => 'error', 'message' => 'Invalid request'];

// --- 1. HANDLE ADD MOVIE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_movie') {
    try {
        $title = $_POST['title'];
        $genre = $_POST['genre'];
        $synopsis = $_POST['synopsis'];
        $price = $_POST['price'];
        $duration = $_POST['duration'];
        $status = $_POST['status'];
        $posterName = '';

        // Handle File Upload
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
            $fileName = time() . '_' . basename($_FILES['poster']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['poster']['tmp_name'], $targetPath)) {
                $posterName = $fileName;
            }
        } elseif (!empty($_POST['poster_url'])) {
            // If user used a URL instead of upload
            $posterName = $_POST['poster_url'];
        }

        $stmt = $pdo->prepare("INSERT INTO movies (title, genre, synopsis, price, duration, status, poster) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $genre, $synopsis, $price, $duration, $status, $posterName]);

        $response = ['status' => 'success', 'message' => 'Movie added successfully!'];
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
    
    // Return JSON response for the AJAX/JS
    echo json_encode($response);
    exit;
}

// --- 2. HANDLE FEATURE MOVIE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'feature_movie') {
    try {
        $movieId = $_POST['movie_id'];

        // Transaction to ensure data consistency
        $pdo->beginTransaction();

        // 1. Un-feature ALL movies first
        $pdo->query("UPDATE movies SET is_featured = 0");

        // 2. Feature the selected movie
        $stmt = $pdo->prepare("UPDATE movies SET is_featured = 1 WHERE id = ?");
        $stmt->execute([$movieId]);

        $pdo->commit();
        $response = ['status' => 'success', 'message' => 'Movie is now featured!'];
    } catch (Exception $e) {
        $pdo->rollBack();
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }

    echo json_encode($response);
    exit;
}
?>