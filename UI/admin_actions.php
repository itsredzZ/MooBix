<?php
session_start();
require_once 'db.php';

$uploadDir = 'UI/uploads';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}


$response = ['status' => 'error', 'message' => 'Invalid request'];

// Tambah movie baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_movie') {
    try {
        $title = $_POST['title'];
        $genre = $_POST['genre'];
        $synopsis = $_POST['synopsis'];
        $price = $_POST['price'];
        $duration = $_POST['duration'];
        $status = $_POST['status'];
        $posterName = '';

        // Tambah poster movie
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
            $fileName = time() . '_' . basename($_FILES['poster']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['poster']['tmp_name'], $targetPath)) {
                $posterName = $fileName;
            }
        } elseif (!empty($_POST['poster_url'])) {
            $posterName = $_POST['poster_url'];
        }

        $stmt = $pdo->prepare("INSERT INTO movies (title, genre, synopsis, price, duration, status, poster) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $genre, $synopsis, $price, $duration, $status, $posterName]);

        $response = ['status' => 'success', 'message' => 'Movie added successfully!'];
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }

    echo json_encode($response);
    exit;
}

// Atur featured movie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'feature_movie') {
    try {
        $movieId = $_POST['movie_id'];

        $pdo->beginTransaction();

        $pdo->query("UPDATE movies SET is_featured = 0");

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

// Edit movie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_movie') {
    try {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $genre = $_POST['genre'];
        $synopsis = $_POST['synopsis'];
        $price = $_POST['price'];
        $duration = $_POST['duration'];
        $status = $_POST['status'];

        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
            $fileName = time() . '_' . basename($_FILES['poster']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['poster']['tmp_name'], $targetPath)) {
                $sql = "UPDATE movies SET title=?, genre=?, synopsis=?, price=?, duration=?, status=?, poster=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $genre, $synopsis, $price, $duration, $status, $fileName, $id]);
            } else {
                throw new Exception("Failed to upload new poster image.");
            }
        } else {
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

// Delete movie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_movie') {
    try {
        $id = $_POST['id'];

        $stmt = $pdo->prepare("SELECT poster FROM movies WHERE id = ?");
        $stmt->execute([$id]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->execute([$id]);

        if ($movie && !empty($movie['poster'])) {
            $filePath = $uploadDir . $movie['poster'];

            if (strpos($movie['poster'], 'http') === false && file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $response = ['status' => 'success', 'message' => 'Movie deleted successfully!'];
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }

    echo json_encode($response);
    exit;
}
