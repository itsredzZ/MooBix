<?php
session_start();
session_destroy(); // Hapus sesi
header("Location: ui_index.php"); // Balik ke halaman utama
exit;
?>