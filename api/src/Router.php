<?php
namespace App;

class Router {
    private array $routes = [];
    private string $prefix = '';

    public function group(string $prefix, callable $callback): void {
        $prev = $this->prefix;
        $this->prefix = $prev . $prefix;
        $callback($this);
        $this->prefix = $prev;
    }

    public function get(string $path, callable ...$handlers): void {
        $this->addRoute('GET', $path, $handlers);
    }

    public function post(string $path, callable ...$handlers): void {
        $this->addRoute('POST', $path, $handlers);
    }

    public function put(string $path, callable ...$handlers): void {
        $this->addRoute('PUT', $path, $handlers);
    }

    public function delete(string $path, callable ...$handlers): void {
        $this->addRoute('DELETE', $path, $handlers);
    }

    public function patch(string $path, callable ...$handlers): void {
        $this->addRoute('PATCH', $path, $handlers);
    }

    private function addRoute(string $method, string $path, array $handlers): void {
        $fullPath = $this->prefix . $path;
        // Convierte :param a regex named groups
        $pattern = preg_replace('/:([a-zA-Z_]+)/', '(?P<$1>[^/]+)', $fullPath);
        $pattern = '#^' . $pattern . '$#';
        $this->routes[] = compact('method', 'pattern', 'handlers', 'fullPath');
    }

    public function dispatch(string $method, string $uri): void {
        // Quitar query string
        $path = parse_url($uri, PHP_URL_PATH);
        // Quitar /api prefix si existe
        if (str_starts_with($path, '/api')) {
            $path = substr($path, 4);
        }
        // Quitar trailing slash (excepto /)
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (preg_match($route['pattern'], $path, $matches)) {
                // Extraer params nombrados
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $req = new Request($params);
                $res = new Response();

                foreach ($route['handlers'] as $handler) {
                    $result = $handler($req, $res);
                    if ($result === false || $res->isSent()) return;
                }
                return;
            }
        }

        // 404
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
}

class Request {
    public array $params;
    public array $query;
    public array $body;
    public array $headers;
    public ?object $user = null;

    public function __construct(array $params = []) {
        $this->params = $params;
        $this->query = $_GET;
        $this->body = json_decode(file_get_contents('php://input'), true) ?? [];
        $this->headers = $this->parseHeaders();
    }

    private function parseHeaders(): array {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;
            }
        }
        // Apache CGI/FPM: el header Authorization se pierde; recuperarlo desde REDIRECT_HTTP_AUTHORIZATION
        if (!isset($headers['authorization'])) {
            $fallback = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;
            if ($fallback) {
                $headers['authorization'] = $fallback;
            }
        }
        return $headers;
    }

    public function getHeader(string $name): ?string {
        return $this->headers[strtolower($name)] ?? null;
    }
}

class Response {
    private bool $sent = false;

    public function isSent(): bool {
        return $this->sent;
    }

    public function status(int $code): self {
        http_response_code($code);
        return $this;
    }

    public function json(mixed $data): void {
        $this->sent = true;
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
