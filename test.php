<?php
$access_token = 'dop_v1_6c47eeb3ac49d7785a011da7032816ea781e4f48d84d3cf065bf01bdfef27d13'; // Replace with the actual access token

$api_url = 'https://syrah-api.centrastage.net/api/v2/site/39362110-3e83-409a-a814-996246bee439/devices/';

$ch = curl_init($api_url);

// Set the HTTP headers including the Authorization header with Bearer token
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $access_token
));

// Set other cURL options as needed
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    echo 'Error: ' . curl_error($ch);
} else {
    echo 'Response: ' . $response;
}

curl_close($ch);
?>
