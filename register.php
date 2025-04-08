<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection
$conn = new mysqli("sql300.infinityfree.com", "if0_38665653", "AbkoThT3WFWZse", "if0_38665653_users");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Registration process
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $username = $conn->real_escape_string($_POST["username"]);
    $email = $conn->real_escape_string($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash the password

    // Check if username or email already exists
    $sql_check = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
    $result = $conn->query($sql_check);
    
    if ($result->num_rows > 0) {
        echo "<p style='color:red;'>❌ Username or Email already exists!</p>";
    } else {
        // Insert into the database
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green;'>✅ Registration successful! You can <a href='login.php'>login</a> now.</p>";
        } else {
            echo "<p style='color:red;'>❌ Error: " . $conn->error . "</p>";
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form method="POST" action="">
        Username: <input type="text" name="username" required><br><br>
        Email: <input type="email" name="email" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        <button type="submit">Register</button>
    </form>
    <br>
    <a href="login.php">Already have an account? Login here</a>
</body>
</html>
