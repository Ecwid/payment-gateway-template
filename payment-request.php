<?php

$client_id = "test-rick-payment-template";
$client_secret = "lfeKILJMFQVc3vXzW79B6TI5VKs8DFeT"; // This is a dummy value. Place your client_secret key here. You received it from Ecwid team in email when registering the app
//$cipher = "AES-128-CBC";
$iv = "abcdefghijklmnopqrstuvwx";// this can be generated random if you plan to store it for later but in this case e.g. openssl_random_pseudo_bytes($ivlen);
$cipher = "aes-128-gcm";
$ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
$tag = 0;

if (isset($_POST["data"])) {

// Functions to decrypt the payment request from Ecwid

    function getEcwidPayload($app_secret_key, $data)
    {
        // Get the encryption key (16 first bytes of the app's client_secret key)
        $encryption_key = substr($app_secret_key, 0, 16);

        // Decrypt payload
        $json_data = aes_128_decrypt($encryption_key, $data);

        // Decode json
        $json_decoded = json_decode($json_data, true);
        return $json_decoded;
    }

    function aes_128_decrypt($key, $data)
    {
        // Ecwid sends data in url-safe base64. Convert the raw data to the original base64 first
        $base64_original = str_replace(array('-', '_'), array('+', '/'), $data);

        // Get binary data
        $decoded = base64_decode($base64_original);

        // Initialization vector is the first 16 bytes of the received data
        $iv = substr($decoded, 0, 16);

        // The payload itself is is the rest of the received data
        $payload = substr($decoded, 16);

        // Decrypt raw binary payload
        $json = openssl_decrypt($payload, "aes-128-cbc", $key, OPENSSL_RAW_DATA, $iv);
        //$json = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $payload, MCRYPT_MODE_CBC, $iv); // You can use this instead of openssl_decrupt, if mcrypt is enabled in your system

        return $json;
    }

    // Get payload from the POST and decrypt it
    $ecwid_payload = $_POST['data'];

    // The resulting JSON from payment request will be in $order variable
    $order = getEcwidPayload($client_secret, $ecwid_payload);

    session_start();
    session_id(md5($iv . $order['cart']['order']['id']));

    // Debug preview of the request decoded earlier
    echo "<h3>REQUEST DETAILS</h3>";

    // Account info from merchant app settings in app interface in Ecwid CP
    $x_account_id = $order['merchantAppSettings']['merchantId'];
    $api_key = $order['merchantAppSettings']['apiKey'];

    // OPTIONAL: Split name field into two fields: first name and last name
    $fullName = explode(" ", $order["cart"]["order"]["billingPerson"]["name"]);
    $firstName = $fullName[0];
    $lastName = $fullName[1];

    // Encode access token and prepare callback URL template
    $ciphertext_raw = openssl_encrypt($order['token'], $cipher, $client_secret, $options = 0, $iv, $tag);
    $callbackPayload = base64_encode($ciphertext_raw);

    // Encode return URL
    $returnUrl_raw = openssl_encrypt($order['returnUrl'], $cipher, $client_secret, $options = 0, $iv, $tag);
    $returnUrlPayload = base64_encode($returnUrl_raw);

    $queryData = http_build_query([
        'storeId' => $order['storeId'],
        'orderNumber' => $order['cart']['order']['id'],
        'callbackPayload' => $callbackPayload,
    ]);

    $callbackUrl = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?{$queryData}";

    $_SESSION["{$order['cart']['order']['id']}_returnUrl"] = $returnUrlPayload;

    // Request parameters to pass into payment gateway
    $request = array(
        "x_account_id" => $x_account_id,
        "x_api_key" => $api_key,
        "x_amount" => $order["cart"]["order"]["total"],
        "x_currency" => $order["cart"]["currency"],
        "x_customer_billing_address1" => str_replace(PHP_EOL, ' ', $order["cart"]["order"]["billingPerson"]["street"]),
        "x_customer_billing_city" => $order["cart"]["order"]["billingPerson"]["city"],
        "x_customer_billing_country" => $order["cart"]["order"]["billingPerson"]["countryCode"],
        "x_customer_billing_state" => $order["cart"]["order"]["billingPerson"]["stateOrProvinceCode"],
        "x_customer_billing_postcode" => $order["cart"]["order"]["billingPerson"]["postalCode"],
        "x_customer_email" => $order["cart"]["order"]["email"],
        "x_customer_first_name" => $firstName,
        "x_customer_last_name" => $lastName,
        "x_customer_phone" => $order["cart"]["order"]["billingPerson"]["phone"],
        "x_customer_shipping_address1" => str_replace(PHP_EOL, ' ', $order["cart"]["order"]["shippingPerson"]["street"]),
        "x_customer_shipping_city" => $order["cart"]["order"]["shippingPerson"]["city"],
        "x_customer_shipping_country" => $order["cart"]["order"]["shippingPerson"]["countryCode"],
        "x_customer_shipping_state" => $order["cart"]["order"]["shippingPerson"]["stateOrProvinceCode"],
        "x_customer_shipping_postcode" => $order["cart"]["order"]["shippingPerson"]["postalCode"],
        "x_customer_shipping_phone" => $order["cart"]["order"]["shippingPerson"]["phone"],
        "x_description" => "Order number" . $order['cart']['order']['referenceTransactionId'],
        "x_reference" => $order['cart']['order']['referenceTransactionId'],
        "x_url_success" => $callbackUrl . "&status=PAID",
        "x_url_error" => $callbackUrl . "&status=CANCELLED",
        "x_url_cancel" => $order["returnUrl"]
    );

    // Sign the payment request
    $signature = payment_sign($request, $api_key);
    $request["x_signature"] = $signature;

    // Print the request variables to debug
    echo "<br/>";
    foreach ($request as $name => $value) {
        echo "$name: $value<br/>";
    }
    echo "<br/>";

    // Print form on a page to submit it from a button press
    echo "<form action='https://example.paymentpage.com/checkout' method='post' id='payment_form'>";
    foreach ($request as $name => $value) {
        echo "<input type='hidden' name='$name' value='$value'></input>";
    }
    echo "<input type='submit' value='Submit'>";
    echo "</form>";
    echo "<script>document.querySelector('#payment_form').submit();</script>";

}

// Function to sign the payment request form
function payment_sign($query, $api_key)
{
    $clear_text = '';
    ksort($query);
    foreach ($query as $key => $value) {
        if (substr($key, 0, 2) === "x_") {
            $clear_text .= $key . $value;
        }
    }
    $hash = hash_hmac("sha256", $clear_text, $api_key);
    return str_replace('-', '', $hash);
}


// If we are returning back to storefront. Callback from payment

if (isset($_GET["callbackPayload"]) && isset($_GET["status"])) {

    session_start();
    session_id(md5($iv . $_GET['orderNumber']));
    // Set variables
    $c = base64_decode($_GET['callbackPayload']);
    $token = openssl_decrypt($c, $cipher, $client_secret, $options = 0, $iv, $tag);
    $storeId = $_GET['storeId'];
    $orderNumber = $_GET['orderNumber'];
    $status = $_GET['status'];
    $r = base64_decode($_SESSION["{$orderNumber}_returnUrl"]);
    $returnUrl = openssl_decrypt($r, $cipher, $client_secret, $options = 0, $iv, $tag);
    session_destroy();

    // Prepare request body for updating the order
    $json = json_encode(array(
        "paymentStatus" => $status,
        "externalTransactionId" => "transaction_" . $orderNumber
    ));

    // URL used to update the order via Ecwid REST API
    $url = "https://app.ecwid.com/api/v3/$storeId/orders/transaction_$orderNumber?token=$token";

    // Send request to update order
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($json)));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // return customer back to storefront
    echo "<script>window.location = '$returnUrl'</script>";

} else {

    header('HTTP/1.0 403 Forbidden');
    echo 'Access forbidden!';

}
