<?php 

// Database connection details
$servername = 'localhost:3306';
$username = 'dev_rmm';
$password = 'zbBm5#227';
$dbname = 'projects_rmm';

echo "Hello<br>";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else{
    echo "Right Connection<br><br>";
}

// Replace with your RMM API endpoint and credentials
$rmmApiUrl = 'https://syrah-api.centrastage.net/api/v2/site/39362110-3e83-409a-a814-996246bee439/alerts/resolved ';
$rmmApiKey = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOlsiYWVtLWFwaSJdLCJ1c2VyX25hbWUiOiJtYWhlc2hAZmx5b25pdC5jb20uYXUiLCJzY29wZSI6WyJkZWZhdWx0Il0sImNsaWVudEF1dGhvcml0aWVzIjpbXSwiYWNjb3VudF91aWQiOiJzeXI4MWI3MDAwMSIsImV4cCI6MTY5NDQyMjU4MSwiaWF0IjoxNjk0MDYyNTgxLCJqdGkiOiIwV3FmTFpnTnNqVUxzb181bkRwSlRpZFhNRjgiLCJjbGllbnRfaWQiOiJwdWJsaWMtY2xpZW50In0.0cKSkJ_HcMkWkM7KkEDPgOT9DbP7Gjm0gvD1tMMsXL-mTR0oN_pL9FbWcjbEUfh0hon22x8dU_Cy3dqccmja8Sm9fSNT0psib0O8OAlcjtY2aw0rRY-plYDflgyRQzzo9iD19BimQabvl9fBUZZFOkLYaI7vCa6bX15MQkmNV7GQbgYMqqAybhRQMsCZLJQ98RoB1Co9IFuf62a3YWah3cSZjrwHKsgnehu8zmT0ApSMTReWtWnLPoE1EYttp9oRHc7oyIm7ypSP7RKQHKsqaNu0QOlu7_ur2lHurAwN9oYYhD1U9LHU4jXDnAdiWmSJzIAvc5jte8I7vGQX1KQ4qw';

// Retrieve data from RMM API
$data = json_decode(file_get_contents($rmmApiUrl, false, stream_context_create([
    'http' => ['header' => "Authorization: Bearer $rmmApiKey"]
])), true);

if( $data ){
    //echo '<pre>'; print_r($data); echo '</pre>';
    echo "<br> All Dtat";
} else {
    echo "<br> Token Issue";
}

// Insert data into MySQL table
foreach ($data['alerts'] as $resolved) {
    $alertUid           = $conn->real_escape_string($resolved['alertUid']);
    $priority           = $conn->real_escape_string($resolved['priority']);
    $diagnostics        = $conn->real_escape_string($resolved['diagnostics']);
    $resolvedAtt        = $resolved['resolvedAtt'] ? 'True' : 'False';
    $resolvedBy         = $conn->real_escape_string($resolved['resolvedBy']);
    $resolvedOn         = $resolved['resolvedOn'] ? date('Y-m-d H:i:s', $resolved['resolvedOn'] / 1000) : null;
    $muted              = $resolved['muted'] ? 'True' : 'False';
    $ticketNumber       = $conn->real_escape_string($resolved['ticketNumber']);
    $timestamp          = date('Y-m-d H:i:s', $resolved['timestamp'] / 1000);
    $sendsEmails        = $resolved['alertMonitorInfo']['sendsEmails'] ? 'True' : 'False';
    $createsTicket      = $resolved['alertMonitorInfo']['createsTicket'] ? 'True': 'False';
    $classAtt           = $conn->real_escape_string($resolved['alertContext']['@class']);
    $serviceName        = $conn->real_escape_string($resolved['alertContext']['serviceName']);
    $status             = $conn->real_escape_string($resolved['alertContext']['status']);
    $deviceUid          = $conn->real_escape_string($resolved['alertSourceInfo']['deviceUid']);
    $deviceName         = $conn->real_escape_string($resolved['alertSourceInfo']['deviceName']);
    $siteUid            = $conn->real_escape_string($resolved['alertSourceInfo']['siteUid']);
    $siteName           = $conn->real_escape_string($resolved['alertSourceInfo']['siteName']);
    $responseActions	= $conn->real_escape_string($resolved['responseActions']);
    $autoresolveMins    = $resolved['autoresolveMins'];

    
    // Insert a new row if the alertUid is not already in the table
    $sql = "INSERT INTO resolved_alert (alertUid, priority, diagnostics, resolved, resolvedBy, resolvedOn, muted, ticketNumber, timestamp, sendsEmails, createsTicket, class, serviceName, status, deviceUid, deviceName, siteUid, siteName, responseActions, autoresolveMins) 
            VALUES ('$alertUid', '$priority', '$diagnostics', '$resolved', '$resolvedBy', '$resolvedOn', '$muted', '$ticketNumber', '$timestamp', '$sendsEmails', '$createsTicket', '$classAtt', '$serviceName', '$status', '$deviceUid', '$deviceName', '$siteUid', '$siteName', '$responseActions', '$autoresolveMins')";
    
        if ($conn->query($sql) === TRUE) {
            echo "Record inserted successfully into resolved_alert table.<br>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
}

$conn->close();

?>