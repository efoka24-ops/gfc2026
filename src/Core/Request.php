<?php
declare(strict_types=1);

namespace Gfc\Core;

final class Request
{
    private function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $body,
        public readonly array $files,
        public readonly array $headers,
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = rtrim(parse_url($uri, PHP_URL_PATH) ?: '/', '/') ?: '/';

        $raw  = file_get_contents('php://input') ?: '';
        $type = $_SERVER['CONTENT_TYPE'] ?? '';
        $body = $_POST;
        if ($raw !== '' && str_contains($type, 'application/json')) {
            $body = json_decode($raw, true) ?: [];
        }

        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $headers[strtolower(str_replace('_', '-', substr($k, 5)))] = $v;
            }
        }

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $path, $_GET, $body, $_FILES, $headers
        );
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function int(string $key, ?int $default = null): ?int
    {
        $v = $this->input($key);
        return $v === null || $v === '' ? $default : (int) $v;
    }

    public function str(string $key, string $default = ''): string
    {
        $v = $this->input($key, $default);
        return is_scalar($v) ? trim((string) $v) : $default;
    }

    public function bearer(): ?string
    {
        $h = $this->headers['authorization'] ?? '';
        return preg_match('/^Bearer\s+(.+)$/i', $h, $m) ? $m[1] : null;
    }

    public function wantsJson(): bool
    {
        return str_starts_with($this->path, '/api');
    }
}
