<?php
declare(strict_types=1);

namespace Gfc\Core;

final class Router
{
    /** @var array<int, array{method:string, regex:string, params:string[], handler:array}> */
    private array $routes = [];

    public function get(string $path, array $handler): void    { $this->add('GET', $path, $handler); }
    public function post(string $path, array $handler): void   { $this->add('POST', $path, $handler); }
    public function delete(string $path, array $handler): void { $this->add('DELETE', $path, $handler); }

    private function add(string $method, string $path, array $handler): void
    {
        $params = [];
        $regex  = preg_replace_callback(
            '/\{(\w+)(?::([^}]+))?\}/',
            static function (array $m) use (&$params): string {
                $params[] = $m[1];
                return '(' . ($m[2] ?? '[^/]+') . ')';
            },
            $path
        );

        $this->routes[] = [
            'method'  => $method,
            'regex'   => '#^' . $regex . '$#',
            'params'  => $params,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $req, Database $db, Auth $auth, array $config): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $req->method) {
                continue;
            }
            if (!preg_match($route['regex'], $req->path, $m)) {
                continue;
            }

            $args = [];
            foreach ($route['params'] as $i => $name) {
                $args[$name] = $m[$i + 1] ?? null;
            }

            [$class, $action] = $route['handler'];
            $controller = new $class($db, $auth, $config);
            $controller->$action($req, $args);
            return;
        }

        if ($req->wantsJson()) {
            Response::error('not_found', 'Route inconnue : ' . $req->path, 404);
        }
        Response::html('<h1>404</h1><p>Page introuvable.</p>', 404);
    }
}
