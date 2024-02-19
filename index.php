<?php
// Database connection details
$servername = '#ServerName';
$username = '#UserName';
$password = '#PassWord';
$dbname = '#DBName';

//echo "Hello";
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
$rmmApiUrl = 'API URL';
$rmmApiKey = 'API Key';

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
