<?php
// Start session
session_start();

// Database connection
$conn = new mysqli("sql300.infinityfree.com", "if0_38665653", "AbkoThT3WFWZse", "if0_38665653_users");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST["username"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            header("Location: dashboard.php");
            exit();
        } else {
            echo "❌ Wrong password!";
        }
    } else {
        echo "❌ User not found!";
    }
}
?>

<!-- HTML Form -->
<h2>Login</h2>
<form method="POST" action="">
  Username: <input type="text" name="username" required><br><br>
  Password: <input type="password" name="password" required><br><br>
  <button type="submit">Login</button>
</form>
