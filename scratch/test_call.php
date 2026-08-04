<?php
$url = 'http://localhost:8000/service_staff/dashboard';
$data = ['action' => 'call_next'];
$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\nCookie: " . (isset($argv[1]) ? $argv[1] : "") . "\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];
$context  = stream_context_create($options);
$result = @file_get_contents($url, false, $context);
echo "Response Headers:\n";
print_r($http_response_header);
if ($result === FALSE) {
    echo "Request failed\n";
} else {
    echo "Request success\n";
}
