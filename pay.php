
<?php
require_once('OAuth.php');
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Your PesaPal credentials
$consumer_key = "ies3S9sPr0wZdYuTYCl0X6xxyEuJaA+C";
$consumer_secret = "r3JZuhNa1qPsv3AQUYRcMqvf3vk=";

// Replace with your own domain
$callback_url = "git@github.com:shepry/repository-name.git/pesapal-ipn.php
";

// User details (normally from database)
$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$email = "shadrackpeter40@gmail.com"; // Replace with real user email
$amount = 10.00; // or get from form input

// Encode the payment details
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

$post_xml = htmlentities($post_xml);

// Generate OAuth signature
$consumer = new OAuthConsumer($consumer_key, $consumer_secret);
$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
$token = NULL;

$params = array('oauth_callback' => $callback_url);
$req = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", "https://pay.pesapal.com/v3/api/PostPesapalDirectOrderV4", $params);
$req->set_parameter("pesapal_request_data", $post_xml);
$req->sign_request($signature_method, $consumer, $token);

// Redirect user to PesaPal
$iframe_src = $req->to_url();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Top Up with PesaPal</title>
</head>
<body>
    <h2>Top Up Your Wallet</h2>
    <iframe src="<?php echo $iframe_src; ?>" width="100%" height="700px" scrolling="no" frameBorder="0">
        <p>Browser unable to load iFrame</p>
    </iframe>
</body>
</html>
