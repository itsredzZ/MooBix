<?php
session_start();
require 'koneksi.php'; // Pastikan file koneksi ada

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cek Database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verifikasi Password
    if ($user && password_verify($password, $user['password'])) {
        // Login Sukses
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        
        // Redirect KEMBALI ke halaman UI utama
        header("Location: ../UI/ui_index.php");
        exit();
    } else {
        $error = "Email atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Cinetix</title>
    </head>
<body style="background-color: #000; color: #f5f5dc; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <div style="background-color: #f5f5dc; padding: 40px; width: 400px; text-align: center; color: black;">
        <h2 style="letter-spacing: 2px;">LOG IN</h2>
        <hr style="border: 1px dashed #ccc; margin-bottom: 30px;">

        <?php if(isset($error)): ?>
            <p style="color: red; font-size: 14px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <div style="text-align: left; margin-bottom: 15px;">
                <label style="font-size: 12px; font-weight: bold;">EMAIL / ID</label><br>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid black; background: #f0f0e0;">
            </div>

            <div style="text-align: left; margin-bottom: 25px;">
                <label style="font-size: 12px; font-weight: bold;">PASSWORD</label><br>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid black; background: #f0f0e0;">
            </div>

            <button type="submit" name="login" style="width: 100%; padding: 15px; background: #3e2f2f; color: white; font-weight: bold; border: none; cursor: pointer;">
                ENTER
            </button>
        </form>

        <br>
        <a href="register.php" style="color: #3e2f2f; font-size: 14px; text-decoration: underline;">Create an Account</a>
    </div>

</body>
</html>