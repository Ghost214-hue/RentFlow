<?php
/**
 * Simple Router
 */
namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public static function sendBaseHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigin = self::getAllowedCorsOrigin($origin);
        if ($allowedOrigin) {
            header('Access-Control-Allow-Origin: ' . $allowedOrigin);
            header('Vary: Origin');
        }
    }

    private static function getAllowedCorsOrigin(string $origin): ?string
    {
        if ($origin === '') {
            return null;
        }

        $allowed = array_filter(array_map(
            'trim',
            explode(',', (string) Env::get('CORS_ALLOWED_ORIGINS', Env::get('APP_URL', '')))
        ));

        $originHost = parse_url($origin, PHP_URL_HOST);
        $requestHost = $_SERVER['HTTP_HOST'] ?? '';
        if ($originHost && $requestHost && strcasecmp($originHost, explode(':', $requestHost)[0]) === 0) {
            return $origin;
        }

        return in_array($origin, $allowed, true) ? $origin : null;
    }

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

        // Initialize security middleware
        \App\Core\SecurityMiddleware::initialize();

        // Remove base path if behind a subdirectory
        // Handle both /api/houses and /rentaflow/api/houses patterns
        $apiPos = strpos($uri, '/api');
        if ($apiPos !== false) {
            $uri = substr($uri, $apiPos + 4); // +4 to skip '/api'
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
                    // Pass params as single array argument for controller methods
                    $controller->$method($params);
                } else {
                    call_user_func_array($handler, array_values($params));
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
        self::sendBaseHeaders();
        
        // Add security headers
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Content-Type: application/json');
        
        // Prevent caching of sensitive responses
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo json_encode($data);
        exit;
    }

    /**
     * Get JSON request body
     */
    private static ?string $rawBodyCache = null;

    public static function getRequestBody(): array
    {
        if (self::$rawBodyCache === null) {
            self::$rawBodyCache = file_get_contents('php://input');
        }
        $data = json_decode(self::$rawBodyCache, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Allow middleware or other handlers to cache the raw body
     * so it's not consumed twice (since php://input can only be read once).
     */
    public static function cacheRawBody(?string $body = null): void
    {
        self::$rawBodyCache = $body ?? file_get_contents('php://input');
    }

    /**
     * Get the authenticated user ID from the request
     */
    public static function getAuthUserId(): ?int
    {
        return $_REQUEST['auth_user_id'] ?? null;
    }

    public static function getAuthRole(): string
    {
        return $_REQUEST['auth_user_role'] ?? 'owner';
    }

    public static function getAuthActorId(): ?int
    {
        return $_REQUEST['auth_actor_id'] ?? self::getAuthUserId();
    }

    public static function requireOwner(): void
    {
        if (self::getAuthRole() !== 'owner') {
            self::jsonResponse(['error' => 'Only owners can perform this action'], 403);
        }
    }

    public static function requireOwnerOrCaretaker(): void
    {
        $role = self::getAuthRole();
        if ($role !== 'owner' && $role !== 'caretaker') {
            self::jsonResponse(['error' => 'Only owners and caretakers can perform this action'], 403);
        }
    }

    public static function getCaretakerPropertyIds(Database $db): array
    {
        if (self::getAuthRole() !== 'caretaker') {
            return [];
        }

        $caretaker = $db->fetchOne(
            "SELECT assigned_properties FROM caretakers WHERE id = ? AND owner_id = ?",
            [self::getAuthActorId(), self::getAuthUserId()]
        );

        if (!$caretaker || empty($caretaker['assigned_properties'])) {
            return [];
        }

        return array_values(array_filter(array_map('intval', explode(',', $caretaker['assigned_properties']))));
    }

    public static function getAuthTenantId(): ?int
    {
        return self::getAuthRole() === 'tenant' ? self::getAuthActorId() : null;
    }
}
