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
    '/verify' => 'verify_otp.php',    
    // Role Dashboards
    '/admin/dashboard' => 'modules/admin/dashboard.php',
    '/information_staff/dashboard' => 'modules/information_staff/dashboard.php',
    '/information_staff/profile' => 'modules/information_staff/profile.php',
    '/information_staff/live_display' => 'modules/information_staff/live_display.php',
    '/information_staff/logout' => 'modules/information_staff/logout.php',
    
    // Service Staff (was staff/queue)
    '/service_staff/dashboard' => 'modules/service_staff/dashboard.php',
    '/service_staff/profile' => 'modules/service_staff/profile.php',
    '/service_staff/live_display' => 'modules/service_staff/live_display.php',
    '/service_staff/logout' => 'modules/service_staff/logout.php',
    '/service_staff/records' => 'modules/service_staff/records/index.php',
    '/api/service_staff/waiting_list' => 'api/service_staff/waiting_list.php',
    '/api/information_staff/recent_tickets' => 'api/information_staff/recent_tickets.php',

    // Admin Modules
    '/admin/services' => 'modules/admin/service_management/services.php',
    '/admin/counters' => 'modules/admin/service_management/counters.php',
    '/admin/records' => 'modules/admin/records.php',
    '/admin/user_management' => 'modules/admin/user_management/index.php',
    '/admin/user_management/add' => 'modules/admin/user_management/add.php',
    '/admin/user_management/edit' => 'modules/admin/user_management/edit.php',
    '/admin/user_management/archive' => 'modules/admin/user_management/archive.php',
    '/admin/user_management/restore' => 'modules/admin/user_management/restore.php',
    '/admin/user_management/resend_setup' => 'modules/admin/user_management/resend_setup.php',
    '/admin/activity_logs' => 'modules/admin/activity_logs.php',
    
    '/admin/settings/profile' => 'modules/admin/settings/profile.php',
    '/admin/settings/media' => 'modules/admin/settings/media.php',
    '/admin/settings/config' => 'modules/admin/settings/config.php',
    
    '/admin/live_display' => 'modules/admin/live_display.php',
    '/admin/logout' => 'modules/admin/logout.php',
    
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
