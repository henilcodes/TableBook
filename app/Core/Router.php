<?php
namespace App\Core;

class Router
{
    private $routes = [];
    private $middleware = [];
    
    public function get($path, $handler, $middleware = [])
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post($path, $handler, $middleware = [])
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    private function addRoute($method, $path, $handler, $middleware = [])
    {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'original' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    public function dispatch($uri, $method)
    {
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Ensure leading slash
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Extract named parameters
                preg_match_all('/\{(\w+)\}/', $route['original'], $paramNames);
                $params = [];
                foreach ($paramNames[1] as $index => $name) {
                    $params[$name] = $matches[$index] ?? null;
                }
                
                // Execute middleware
                foreach ($route['middleware'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle()) {
                        return false;
                    }
                }
                
                // Execute handler
                $handler = $route['handler'];
                if (is_array($handler)) {
                    $controller = new $handler[0]();
                    $method = $handler[1];
                    return $controller->$method($params);
                } else {
                    return call_user_func($handler, $params);
                }
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        require_once APP_PATH . '/Views/errors/404.php';
        return false;
    }
}

