<?php
session_start();
require 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['reg_password'];
    $confirm = $_POST['confirm_password'];

    // Basic Validation
    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: ui_index.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user'; 

    try {
        // Check if email exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if($check->rowCount() > 0){
            $_SESSION['error'] = "Email already registered!";
            header("Location: ui_index.php");
            exit();
        }

        // Insert New User
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fullname, $email, $hashed_password, $role]);

        $_SESSION['success'] = "Registration successful! Please Login.";
        header("Location: ui_index.php");

    } catch (PDOException $e) {
        $_SESSION['error'] = "System Error: " . $e->getMessage();
        header("Location: ui_index.php");
    }
}
?>