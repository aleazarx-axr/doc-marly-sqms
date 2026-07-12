<?php
// Autoloader or manual requires for core components
require_once __DIR__ . '/../app/core/Router.php';

// Instantiate Router
$router = new Router();

// Load Routes
$routes = require_once __DIR__ . '/../config/routes.php';

foreach ($routes as $uri => $controllerAction) {
    $router->add($uri, $controllerAction);
}

// Dispatch
$uri = $_SERVER['REQUEST_URI'];
// Handle running in a subfolder (if applicable) or built-in server
if (php_sapi_name() === 'cli-server') {
    // If it's a direct file request for an asset, let the server handle it
    $path = __DIR__ . parse_url($uri, PHP_URL_PATH);
    if ($uri !== '/' && file_exists($path) && is_file($path)) {
        return false; 
    }
}

$router->dispatch($uri);
?>
