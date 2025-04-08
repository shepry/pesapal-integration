<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("sql300.infinityfree.com", "if0_38665653", "AbkoThT3WFWZse", "if0_38665653_users");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION["user_id"];

// Get user info
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Handle payout request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["withdraw"])) {
    $amount = floatval($_POST["amount"]);
    $phone = $conn->real_escape_string($_POST["phone"]);

    // Phone validation (Kenyan format)
    if (!preg_match("/^\+254\d{9}$/", $phone)) {
        echo "<p style='color:red;'>❌ Invalid phone number format! Ensure it's a valid Kenyan number.</p>";
    } elseif ($amount > 0 && $amount <= $user["balance"]) {
        // Insert withdrawal request
        $sql = "INSERT INTO if0_38665653_withdrawals.withdrawals (user_id, phone, amount) VALUES ($user_id, '$phone', $amount)";
        if ($conn->query($sql)) {
            echo "<p style='color:green;'>✅ Withdrawal request submitted successfully!</p>";

            // Update balance
            $new_balance = $user["balance"] - $amount;
            $conn->query("UPDATE users SET balance = $new_balance WHERE id = $user_id");

            // Refresh balance
            $user["balance"] = $new_balance;
        } else {
            echo "<p style='color:red;'>❌ Error: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ Invalid withdrawal amount!</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($user["username"]); ?>!</h2>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user["email"]); ?></p>
    <p><strong>Balance:</strong> KES <?php echo number_format($user["balance"], 2); ?></p>

    <hr>

    <h3>Request Withdrawal</h3>
    <form method="POST" action="">
        Amount (KES): <input type="number" step="0.01" name="amount" required><br><br>
        Phone Number: <input type="text" name="phone" required><br><br>
        <button type="submit" name="withdraw">Request Payout</button>
        <a href="pay.php">💳 Top Up Balance</a>

    </form>

    <br><br>
    <a href="logout.php">Logout</a>
</body>
</html>
