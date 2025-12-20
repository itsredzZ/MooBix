<?php
session_start();
session_destroy();
header("Location: ../UI/ui_index.php"); // Kembali ke halaman utama
exit;
