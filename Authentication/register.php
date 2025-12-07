<?php
require 'koneksi.php';

if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Enkripsi Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama, $email, $hashed_password]);

        // Setelah daftar, langsung lempar ke Login
        echo "<script>alert('Akun berhasil dibuat! Silakan Login.'); window.location='login.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Gagal daftar (Email mungkin sudah dipakai).');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<body style="background-color: #000; color: #f5f5dc; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <div style="background-color: #f5f5dc; padding: 40px; width: 400px; text-align: center; color: black;">
        <h2>CREATE ACCOUNT</h2>
        <form method="POST">
            <input type="text" name="nama" placeholder="Full Name" required style="width: 100%; margin-bottom: 10px; padding: 10px;"><br>
            <input type="email" name="email" placeholder="Email" required style="width: 100%; margin-bottom: 10px; padding: 10px;"><br>
            <input type="password" name="password" placeholder="Password" required style="width: 100%; margin-bottom: 20px; padding: 10px;"><br>
            <button type="submit" name="register" style="width: 100%; padding: 15px; background: #3e2f2f; color: white;">SIGN UP</button>
        </form>
        <br>
        <a href="login.php" style="color: black;">Back to Login</a>
    </div>

</body>
</html>