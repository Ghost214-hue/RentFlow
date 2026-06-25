<?php
/**
 * Simple Router
 */
namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    /**
     * Helper: GET route
     */
    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Helper: POST route
     */
    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Helper: PUT route
     */
    public function put(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Helper: DELETE route
     */
    public function delete(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Register a route
     */
    public function addRoute(string $method, string $path, callable|array $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * Register global middleware
     */
    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Dispatch the current request
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove base path if behind a subdirectory
        $basePath = '/api';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        if (empty($uri)) {
            $uri = '/';
        }

        // Run global middleware
        foreach ($this->middleware as $mw) {
            $mw();
        }

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchPath($route['path'], $uri);
            if ($params !== false) {
                // Run route-specific middleware
                foreach ($route['middleware'] as $mw) {
                    $mw();
                }

                // Call handler
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $method] = $handler;
                    $controller = new $class();
                    call_user_func_array([$controller, $method], $params);
                } else {
                    call_user_func_array($handler, $params);
                }
                return;
            }
        }

        // No route matched
        self::jsonResponse(['error' => 'Route not found', 'path' => $uri], 404);
    }

    /**
     * Match a route path against the request URI
     */
    private function matchPath(string $routePath, string $uri): array|false
    {
        // Convert route placeholders like {id} to regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    /**
     * Send a JSON response
     */
    public static function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        echo json_encode($data);
        exit;
    }

    /**
     * Get JSON request body
     */
    public static function getRequestBody(): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Get the authenticated user ID from the request
     */
    public static function getAuthUserId(): ?int
    {
        return $_REQUEST['auth_user_id'] ?? null;
    }
}