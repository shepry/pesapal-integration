<?php
// Replace with your Consumer Key and Secret
$consumer_key = 'ies3S9sPr0wZdYuTYCl0X6xxyEuJaA+C';
$consumer_secret = 'r3JZuhNa1qPsv3AQUYRcMqvf3vk=';

// Step 1: Get the notification details from PesaPal
$tracking_id = $_GET['pesapal_transaction_tracking_id'];
$merchant_ref = $_GET['pesapal_merchant_reference'];

// Step 2: Get OAuth token
$token_url = "https://pay.pesapal.com/v3/api/Auth/RequestToken";
$credentials = base64_encode("$consumer_key:$consumer_secret");

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => $token_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        "Authorization: Basic $credentials"
    )
));

$response = curl_exec($curl);
curl_close($curl);
$data = json_decode($response, true);

if (!isset($data['token'])) {
    die("❌ Failed to get token.");
}
$token = $data['token'];

// Step 3: Get transaction status
$status_url = "https://pay.pesapal.com/v3/api/Transactions/GetTransactionStatus?orderTrackingId=$tracking_id";

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => $status_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $token"
    )
));
$response = curl_exec($curl);
curl_close($curl);

$status_data = json_decode($response, true);

// Now handle the response (you can update DB here)
if ($status_data['payment_status'] == "COMPLETED") {
    // Example: update user balance or order status
    // You should connect to your DB and apply your logic

    // Sample DB connection (adjust if needed)
    $conn = new mysqli("sql300.infinityfree.com", "if0_38665653", "AbkoThT3WFWZse", "if0_38665653_users");
    if ($conn->connect_error) {
        die("DB Connection failed: " . $conn->connect_error);
    }

    // Use merchant reference to identify user or order
    // You must store and match this when user initiates payment
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $amount = $status_data['amount'];
    $user_id = $merchant_ref; // assuming you passed user ID as reference
    $stmt->bind_param("di", $amount, $user_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo "✅ Payment received and balance updated.";
} else {
    echo "⚠️ Payment not completed. Status: " . $status_data['payment_status'];
}
?>
