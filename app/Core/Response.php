<?php

namespace App\Core;

class Response
{
    public function setStatusCode(int $code): self
    {
        http_response_code($code);
        return $this;
    }

    public function header(string $key, string $value): self
    {
        header("$key: $value");
        return $this;
    }

    public function redirect(string $url, int $statusCode = 302): void
    {
        $target = (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))
            ? $url
            : base_url($url);

        http_response_code($statusCode);
        header("Location: $target");
        exit;
    }

    public function json($data, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode);
        $this->header('Content-Type', 'application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function html(string $content, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode);
        $this->header('Content-Type', 'text/html; charset=utf-8');
        echo $content;
        exit;
    }
}

