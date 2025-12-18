<?php
session_start();
require_once 'db.php';

// // Define upload directory (relative to this file)
// // Based on your ui_index.php, images seem to be in '../ui/uploads/'
// $uploadDir = 'UI/uploads';
// if (!file_exists($uploadDir)) {
//     mkdir($uploadDir, 0777, true);
// }

// We check multiple possible locations for the 'uploads' folder
$possiblePaths = [
    __DIR__ . '/uploads/',       // 1. If admin_actions.php is INSIDE the UI folder
    __DIR__ . '/UI/uploads/',    // 2. If admin_actions.php is in the ROOT folder
    __DIR__ . '/../UI/uploads/'  // 3. One level up then into UI (Standard relative)
];

$uploadDir = false;

// Loop through possibilities and pick the first one that actually exists
foreach ($possiblePaths as $path) {
    if (is_dir($path)) {
        $uploadDir = $path;
        break;
    }
}

// ERROR HANDLER: If we still can't find it, stop everything and tell us why.
if ($uploadDir === false) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'System Error: "uploads" folder not found. Please create a folder named "uploads" inside your UI folder.'
    ]);
    exit;
}
// ----------------------------

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

// --- 3. HANDLE EDIT MOVIE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_movie') {
    try {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $genre = $_POST['genre'];
        $synopsis = $_POST['synopsis'];
        $price = $_POST['price'];
        $duration = $_POST['duration'];
        $status = $_POST['status'];
        
        // Check if a new poster was uploaded
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
            $fileName = time() . '_' . basename($_FILES['poster']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['poster']['tmp_name'], $targetPath)) {
                // Update WITH new poster
                $sql = "UPDATE movies SET title=?, genre=?, synopsis=?, price=?, duration=?, status=?, poster=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $genre, $synopsis, $price, $duration, $status, $fileName, $id]);
            } else {
                throw new Exception("Failed to upload new poster image.");
            }
        } else {
            // Update WITHOUT changing the poster (keep the old one)
            $sql = "UPDATE movies SET title=?, genre=?, synopsis=?, price=?, duration=?, status=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $genre, $synopsis, $price, $duration, $status, $id]);
        }

        $response = ['status' => 'success', 'message' => 'Movie updated successfully!'];
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()];
    }

    echo json_encode($response);
    exit;
}

// --- 4. HANDLE DELETE MOVIE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_movie') {
    try {
        $id = $_POST['id'];

        // 1. Get the poster filename first (so we can delete the file)
        $stmt = $pdo->prepare("SELECT poster FROM movies WHERE id = ?");
        $stmt->execute([$id]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Delete from Database
        $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->execute([$id]);

        // 3. Delete the physical file if it exists and is not a URL
        if ($movie && !empty($movie['poster'])) {
            $filePath = $uploadDir . $movie['poster'];
            
            // Only delete if it's a file on our server (not a URL) and it exists
            if (strpos($movie['poster'], 'http') === false && file_exists($filePath)) {
                unlink($filePath); // This command deletes the file
            }
        }

        $response = ['status' => 'success', 'message' => 'Movie deleted successfully!'];
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }

    echo json_encode($response);
    exit;
}
?>