<?php
session_start();
require 'db.php'; // Or 'koneksi.php', check your filename!

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['username']; 
    $password = $_POST['password'];

    // 1. Check Database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR name = ?");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Verify Password
    if ($user && password_verify($password, $user['password'])) {
        
        // SET SESSION DATA
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // 3. THE CRITICAL REDIRECT
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();

    } else {
        // Login Failed - Send back to index with error
        $_SESSION['error'] = "Email or Password incorrect!";
        header("Location: ui_index.php");
        exit();
    }
}
?>