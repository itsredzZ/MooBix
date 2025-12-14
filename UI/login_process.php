<?php
session_start();
require 'db.php'; // Sambung ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['username']; // Di form name-nya 'username', tapi isinya email
    $password = $_POST['password'];

    // 1. Cari user berdasarkan email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Cek apakah user ketemu DAN password cocok
    if ($user && password_verify($password, $user['password'])) {
        
        // --- LOGIN SUKSES ---
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        // Redirect sesuai role (Admin ke Dashboard, User ke Home)
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: ui_index.php");
        }
        exit;

    } else {
        // --- LOGIN GAGAL ---
        echo "<script>
            alert('Email atau Password salah!');
            window.location.href = 'ui_index.php';
        </script>";
    }
}
?>