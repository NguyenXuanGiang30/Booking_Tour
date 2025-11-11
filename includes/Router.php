<?php
class Router {
    private $routes = [];

    public function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Remove base path if defined
        if (defined('BASE_PATH') && BASE_PATH !== '') {
            $basePath = BASE_PATH;
            // Ensure BASE_PATH has a leading slash for comparison
            if (strpos($basePath, '/') !== 0) {
                $basePath = '/' . $basePath;
            }
            
            // Remove base path from the beginning of the path
            if ($path === $basePath || $path === $basePath . '/') {
                // Exact match, set to root
                $path = '/';
            } elseif (strpos($path, $basePath . '/') === 0) {
                // Path starts with base path, remove it
                $path = substr($path, strlen($basePath));
            }
        }
        
        // Normalize path: remove trailing slash except for root
        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);
            
            if ($route['method'] === $method && preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match
                
                if (is_callable($route['handler'])) {
                    return call_user_func_array($route['handler'], $matches);
                } elseif (is_string($route['handler'])) {
                    // Check if it's a process file
                    if (strpos($route['handler'], '_process.php') !== false || strpos($route['handler'], 'handle/') !== false) {
                        $processFile = __DIR__ . "/../handle/{$route['handler']}";
                        if (file_exists($processFile)) {
                            return require_once $processFile;
                        }
                    }
                    // Check if it's controller@method format
                    elseif (strpos($route['handler'], '@') !== false) {
                        list($controller, $method) = explode('@', $route['handler']);
                        $controllerFile = __DIR__ . "/../controllers/{$controller}.php";
                        if (file_exists($controllerFile)) {
                            require_once $controllerFile;
                            $controllerInstance = new $controller();
                            return call_user_func_array([$controllerInstance, $method], $matches);
                        }
                    }
                }
            }
        }

        // 404
        http_response_code(404);
        require_once __DIR__ . '/../views/404.php';
    }

    private function convertToRegex($path) {
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
