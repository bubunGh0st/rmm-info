<?php
$access_token = 'Your Token'; // Replace with the actual access token

$api_url = 'API URL';

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
