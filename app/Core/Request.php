<?php

namespace App\Core;

class Request
{
    private array $data;
    private array $files;
    private array $server;

    public function __construct()
    {
        $this->server = $_SERVER;
        $this->files = $_FILES;

        // Check for JSON request body
        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            $jsonDecoded = json_decode($rawInput, true);
            $this->data = array_merge($_GET, is_array($jsonDecoded) ? $jsonDecoded : []);
        } else {
            $this->data = array_merge($_GET, $_POST);
        }
    }

    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    public function isAjax(): bool
    {
        return (!empty($this->server['HTTP_X_REQUESTED_WITH']) &&
            strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (stripos($this->server['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
    }

    public function input(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->data;
        }

        if (!array_key_exists($key, $this->data)) {
            return $default;
        }

        $val = $this->data[$key];
        if (is_string($val)) {
            return trim($val);
        }

        return $val;
    }

    public function query(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }

        if (!array_key_exists($key, $_GET)) {
            return $default;
        }

        $val = $_GET[$key];
        if (is_string($val)) {
            return trim($val);
        }

        return $val;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function getUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }
        return '/' . trim($uri, '/');
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? 'Unknown';
    }
}

