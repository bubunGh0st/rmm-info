<?php 

// Database connection details
$servername = '#ServerName';
$username = '#UserName';
$password = '#PassWord';
$dbname = '#DBName';

//echo "Hello<br>";
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
$rmmApiUrl = 'API Url';
$rmmApiKey = 'API Key';

// Retrieve data from RMM API
$data = json_decode(file_get_contents($rmmApiUrl, false, stream_context_create([
    'http' => ['header' => "Authorization: Bearer $rmmApiKey"]
])), true);

//confirm to records fetched 
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
