<?php
class Router {
    private $routes = [];

    public function add($route, $controllerAction) {
        $this->routes[$route] = $controllerAction;
    }

    public function dispatch($uri) {
        // Strip query string
        $uri = strtok($uri, '?');
        
        if (array_key_exists($uri, $this->routes)) {
            $parts = explode('@', $this->routes[$uri]);
            
            // If it maps directly to a script file
            if (count($parts) == 1) {
                $filePath = $parts[0];
                if (strpos($filePath, '?') !== false) {
                    $pathParts = explode('?', $filePath);
                    $filePath = $pathParts[0];
                    parse_str($pathParts[1], $queryParams);
                    $_GET = array_merge($_GET, $queryParams);
                }
                require_once __DIR__ . '/../' . $filePath;
                return;
            }

            // Standard MVC controller routing
            $controllerName = $parts[0];
            $method = $parts[1];

            require_once __DIR__ . '/../controllers/' . $controllerName . '.php';
            $controller = new $controllerName();
            $controller->$method();
        } else {
            http_response_code(404);
            echo "<h1>404 Not Found</h1>";
            echo "<p>The requested URL {$uri} was not found on this server.</p>";
        }
    }
}
?>
