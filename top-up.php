<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Your PesaPal credentials
$consumer_key = "ies3S9sPr0wZdYuTYCl0X6xxyEuJaA+C"; // Replace with your actual PesaPal consumer key
$consumer_secret = "r3JZuhNa1qPsv3AQUYRcMqvf3vk="; // Replace with your actual PesaPal consumer secret

// Replace with your own domain and IPN listener URL
$callback_url = "https://todayonline.free.nf/pesapal-ipn.php";

// User details (normally from database)
$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$email = "shadrackpeter40@gmail.com"; // Replace with real user email
$amount = 10.00; // Amount to top-up

// Encode the payment details in XML format
$post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<PesapalDirectOrderInfo 
    xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" 
    xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" 
    Amount=\"$amount\" 
    Description=\"Account top-up for $username\" 
    Type=\"MERCHANT\" 
    Reference=\"$user_id-" . time() . "\" 
    FirstName=\"$username\" 
    LastName=\"\" 
    Email=\"$email\" 
    PhoneNumber=\"\" 
    xmlns=\"http://www.pesapal.com\" />";

// Ensure the XML is properly encoded
$post_xml = htmlentities($post_xml);

// Include the OAuth class
require_once('OAuth.php');

// Generate OAuth signature
$consumer = new OAuthConsumer($consumer_key, $consumer_secret);
$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
$token = NULL;

$params = array('oauth_callback' => $callback_url);
$req = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", "https://pay.pesapal.com/v3/api/PostPesapalDirectOrderV4", $params);
$req->set_parameter("pesapal_request_data", $post_xml);
$req->sign_request($signature_method, $consumer, $token);

// Redirect the user to PesaPal for payment
$iframe_src = $req->to_url();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Up Your Wallet</title>
</head>
<body>
    <h2>Top Up Your Wallet</h2>
    <p>Click the button below to top up your wallet using PesaPal:</p>
    <iframe src="<?php echo $iframe_src; ?>" width="100%" height="700px" scrolling="no" frameborder="0">
        <p>Browser unable to load iFrame. Please use a compatible browser.</p>
    </iframe>
</body>
</html>
