<?php
// Database connection details
$servername = 'localhost:3306';
$username = 'dev_rmm';
$password = 'zbBm5#227';
$dbname = 'projects_rmm';

echo "Hello";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else{
    echo "Right Connection";
}

// Replace with your RMM API endpoint and credentials
$rmmApiUrl = 'https://syrah-api.centrastage.net/api/v2/site/39362110-3e83-409a-a814-996246bee439/alerts/open';
$rmmApiKey = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOlsiYWVtLWFwaSJdLCJ1c2VyX25hbWUiOiJtYWhlc2hAZmx5b25pdC5jb20uYXUiLCJzY29wZSI6WyJkZWZhdWx0Il0sImNsaWVudEF1dGhvcml0aWVzIjpbXSwiYWNjb3VudF91aWQiOiJzeXI4MWI3MDAwMSIsImV4cCI6MTY5NDQyMjU4MSwiaWF0IjoxNjk0MDYyNTgxLCJqdGkiOiIwV3FmTFpnTnNqVUxzb181bkRwSlRpZFhNRjgiLCJjbGllbnRfaWQiOiJwdWJsaWMtY2xpZW50In0.0cKSkJ_HcMkWkM7KkEDPgOT9DbP7Gjm0gvD1tMMsXL-mTR0oN_pL9FbWcjbEUfh0hon22x8dU_Cy3dqccmja8Sm9fSNT0psib0O8OAlcjtY2aw0rRY-plYDflgyRQzzo9iD19BimQabvl9fBUZZFOkLYaI7vCa6bX15MQkmNV7GQbgYMqqAybhRQMsCZLJQ98RoB1Co9IFuf62a3YWah3cSZjrwHKsgnehu8zmT0ApSMTReWtWnLPoE1EYttp9oRHc7oyIm7ypSP7RKQHKsqaNu0QOlu7_ur2lHurAwN9oYYhD1U9LHU4jXDnAdiWmSJzIAvc5jte8I7vGQX1KQ4qw';

// Retrieve data from RMM API
$data = json_decode(file_get_contents($rmmApiUrl, false, stream_context_create([
    'http' => ['header' => "Authorization: Bearer $rmmApiKey"]
])), true);

echo '<pre>'; print_r($data); echo '</pre>';

// Insert data into MySQL table
foreach ($data['alerts'] as $alert) {
    $alertUid           = $conn->real_escape_string($alert['alertUid']);
    $priority           = $conn->real_escape_string($alert['priority']);
    $diagnostics        = $conn->real_escape_string($alert['diagnostics']);
    $resolved           = $alert['resolved'] ? 'True' : 'False';
    $resolvedBy         = $conn->real_escape_string($alert['resolvedBy']);
    $resolvedOn         = $alert['resolvedOn'] ? date('Y-m-d H:i:s', $alert['resolvedOn'] / 1000) : null;
    $muted              = $alert['muted'] ? 'True' : 'False';
    $ticketNumber       = $conn->real_escape_string($alert['ticketNumber']);
    $timestamp          = date('Y-m-d H:i:s', $alert['timestamp'] / 1000);
    $sendsEmails        = $alert['alertMonitorInfo']['sendsEmails'] ? 'True' : 'False';
    $createsTicket      = $alert['alertMonitorInfo']['createsTicket'] ? 'True': 'False';
    $classAtt           = $conn->real_escape_string($alert['alertContext']['@class']);
    $logName            = $conn->real_escape_string($alert['alertContext']['logName']);
    $code               = $conn->real_escape_string($alert['alertContext']['code']);
    $type               = $conn->real_escape_string($alert['alertContext']['type']);
    $source             = $conn->real_escape_string($alert['alertContext']['source']);
    $description        = $conn->real_escape_string($alert['alertContext']['description']);
    $triggerCount       = $alert['alertContext']['triggerCount'];
    $lastTriggered      = date('Y-m-d H:i:s', $alert['alertContext']['lastTriggered'] / 1000);
    $causedSuspension   = $alert['alertContext']['causedSuspension'] ? 'True' : 'False';
    $deviceUid          = $conn->real_escape_string($alert['alertSourceInfo']['deviceUid']);
    $deviceName         = $conn->real_escape_string($alert['alertSourceInfo']['deviceName']);
    $siteUid            = $conn->real_escape_string($alert['alertSourceInfo']['siteUid']);
    $siteName           = $conn->real_escape_string($alert['alertSourceInfo']['siteName']);
    $autoresolveMins    = $alert['autoresolveMins'];

    $sql = "INSERT INTO alerts (alertUid, priority, diagnostics, resolved, resolvedBy, resolvedOn, muted, ticketNumber, timestamp, sendsEmails, createsTicket, classAtt, logName, code, type, source, description, triggerCount, lastTriggered, causedSuspension, deviceUid, deviceName, siteUid, siteName, autoresolveMins) 
            VALUES ('$alertUid', '$priority', '$diagnostics', $resolved, '$resolvedBy', '$resolvedOn', $muted, '$ticketNumber', '$timestamp', $sendsEmails, $createsTicket, '$classAtt', '$logName', '$code', '$type', '$source', '$description', $triggerCount, '$lastTriggered', $causedSuspension, '$deviceUid', '$deviceName', '$siteUid', '$siteName', $autoresolveMins)";

    if ($conn->query($sql) === TRUE) {
        echo "Record inserted successfully into alerts table.<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    if (isset($alert['responseActions']) && is_array($alert['responseActions'])) {
        foreach ($alert['responseActions'] as $responseAction) {
            $actionTime         = date('Y-m-d H:i:s', $responseAction['actionTime'] / 1000);
            $actionType         = $conn->real_escape_string($responseAction['actionType']);
            $description        = $conn->real_escape_string($responseAction['description']);
            $actionReference    = $conn->real_escape_string($responseAction['actionReference']);
            $actionReferenceInt = isset($responseAction['actionReferenceInt']) ? "'" . $conn->real_escape_string($responseAction['actionReferenceInt']) . "'" : 'NULL';

            $sql = "INSERT INTO responseActions (alertUid, actionTime, actionType, description, actionReference, actionReferenceInt) 
                    VALUES ('$alertUid', '$actionTime', '$actionType', '$description', '$actionReference', $actionReferenceInt)";

            if ($conn->query($sql) === TRUE) {
                echo "Record inserted successfully into responseActions table.<br>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

$conn->close();
?>