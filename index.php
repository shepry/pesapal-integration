<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Redirect if logged in
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome to My Payment System</title>
</head>
<body>
    <h1>👋 Welcome to Our Payout Platform</h1>
    <p>This platform lets you register, log in, check your balance, and request payouts easily!</p>

    <a href="register.php">Register</a> |
    <a href="login.php">Login</a>
</body>
</html>
