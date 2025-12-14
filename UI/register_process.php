<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Enkripsi password (biar aman dan sesuai format database)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user'; // Default role user biasa

    try {
        // Masukkan ke database
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password, $role]);

        echo "<script>
            alert('Registrasi Berhasil! Silakan Login.');
            window.location.href = 'ui_index.php';
        </script>";

    } catch (PDOException $e) {
        // Jika email sudah ada
        if ($e->getCode() == 23000) {
            echo "<script>alert('Email sudah terdaftar!'); window.location.href = 'ui_index.php';</script>";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>