<?php
// Test POST request to dashboard
$url = 'http://localhost:8000/information_staff/dashboard';
$data = ['action' => 'issue_ticket', 'name' => 'John Doe', 'service_id' => '4', 'citizen_category' => 'Regular'];
$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
if ($result === FALSE) {
    echo "Request failed\n";
} else {
    // Check if redirect header exists
    foreach ($http_response_header as $header) {
        if (strpos($header, 'Location:') === 0) {
            echo "Redirected to: " . $header . "\n";
            exit;
        }
    }
    echo "No redirect, response length: " . strlen($result) . "\n";
}
