<?php
$host = 'localhost';
// SESUAIKAN DENGAN NAMA DATABASE YANG BARU KAMU BUAT
$dbname = 'db_moobix_1'; 
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>