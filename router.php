<?php
// PHP Built-in Server static file routing
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    if (is_file(__DIR__ . $path)) {
        return false; // serve the requested resource as-is.
    }
}

// Simple Router
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = ''; // Adjust if deployed in a subfolder

// Strip base path if needed
if (!empty($base_path) && strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Routes mapping clean URLs to actual PHP files
$routes = [
    '/' => 'index.php',
    '/login' => 'login.php',
    '/logout' => 'logout.php',
    '/display' => 'display.php',
    '/display_queue.php' => 'display_queue.php', // keep direct access for iframe
    '/setup.php' => 'setup.php',
    '/verify_otp.php' => 'verify_otp.php', // keeping original for login flows if any
    
    // Role Dashboards
    '/admin/dashboard' => 'modules/admin/dashboard.php',
    '/information_staff/dashboard' => 'modules/information_staff/index.php',
    
    // Service Staff (was staff/queue)
    '/service_staff/queue' => 'modules/service_staff/queue/index.php',
    '/service_staff/records' => 'modules/service_staff/records/index.php',
    '/api/service_staff/waiting_list' => 'api/service_staff/waiting_list.php',
    '/api/information_staff/recent_tickets' => 'api/information_staff/recent_tickets.php',

    // Admin Modules
    '/admin/services' => 'modules/admin/service_management/services.php',
    '/admin/counters' => 'modules/admin/service_management/counters.php',
    '/admin/media' => 'modules/admin/media/index.php',
    '/admin/users' => 'modules/admin/users/index.php',
    '/admin/users/add' => 'modules/admin/users/add.php',
    '/admin/users/edit' => 'modules/admin/users/edit.php',
    '/admin/users/archive' => 'modules/admin/users/archive.php',
    '/admin/users/restore' => 'modules/admin/users/restore.php',
    '/admin/users/resend_setup' => 'modules/admin/users/resend_setup.php',
    '/admin/records' => 'modules/admin/records/index.php',
    '/admin/settings' => 'modules/admin/settings/index.php',
    '/admin/logs' => 'modules/admin/logs/index.php',
    
    '/profile' => 'profile.php',
];

if (array_key_exists($request_uri, $routes)) {
    require __DIR__ . '/' . $routes[$request_uri];
} else {
    // 404 Not Found
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>The page you requested was not found.</p>";
}
