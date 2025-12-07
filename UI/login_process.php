<?php
session_start();
$host = 'localhost'; $dbname = 'db_moobix'; $user = 'root'; $pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    
    // Ambil data dari form
    $username = $_POST['username']; // Di form name="username" (bisa email/id)
    $password = $_POST['password'];

    // Cek user di database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        // Login Sukses
        $_SESSION['user_name'] = $user['fullname'];
        header("Location: ui_index.php");
    } else {
        // Login Gagal
        echo "<script>alert('Email atau Password Salah!'); window.location='ui_index.php';</script>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>