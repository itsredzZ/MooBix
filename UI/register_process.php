<?php
session_start();

// Konfigurasi Database
$host = 'localhost';
$dbname = 'db_moobix';
$user = 'root';
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil data dari form Register
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // --- PERBAIKAN DISINI ---
        // Kita ambil input dengan name="name" sesuai HTML kamu
        $name = $_POST['name']; 
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Cek apakah email sudah terdaftar sebelumnya?
        $checkStmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        
        if ($checkStmt->rowCount() > 0) {
            // Jika email sudah ada
            echo "<script>
                    alert('Email sudah terdaftar! Gunakan email lain.');
                    window.location.href = 'ui_index.php';
                  </script>";
        } else {
            // --- PERBAIKAN QUERY SQL DISINI ---
            // Masukkan ke kolom 'name' (bukan fullname)
            $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
            $stmt = $pdo->prepare($sql);
            
            // Eksekusi dengan variabel $name
            $stmt->execute([$name, $email, $password]);

            // Sukses
            echo "<script>
                    alert('Registrasi Berhasil! Silakan Login.');
                    window.location.href = 'ui_index.php';
                  </script>";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>