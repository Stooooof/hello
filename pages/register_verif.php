<?php
session_start();
include('connexion_db.php');

if (isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $checkEmail = $conn->query("SELECT email FROM users WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = 'EMAIL DEJA EXISTANT';
        header("Location: register.php");
        exit();

    } else {
        $conn->query("INSERT INTO users (email, password, role) VALUES ('$email', '$password', '$role')");
        header("Location: login.php");
        exit();
    }
}
?>
