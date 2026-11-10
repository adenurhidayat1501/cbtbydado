<?php

namespace App\Core;

class Controller
{
    protected Request $request;
    protected Response $response;

    public function __construct(?Request $request = null, ?Response $response = null)
    {
        $this->request = $request ?? new Request();
        $this->response = $response ?? new Response();
    }

    protected function render(string $view, array $data = [], string $layout = 'layouts/master'): void
    {
        $viewPath = __DIR__ . '/../Views/' . ltrim($view, '/') . '.php';
        $layoutPath = __DIR__ . '/../Views/' . ltrim($layout, '/') . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file [{$view}] not found at {$viewPath}");
        }

        // Extract variables to view scope
        extract($data);

        // Capture view content buffer
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if ($layout === '' || !file_exists($layoutPath)) {
            echo $content;
            return;
        }

        // Render layout wrapping the content
        require $layoutPath;
    }

    protected function view(string $view, array $data = []): void
    {
        $this->render($view, $data, '');
    }

    protected function json($data, int $statusCode = 200): void
    {
        $this->response->json($data, $statusCode);
    }

    protected function redirect(string $url, int $statusCode = 302): void
    {
        $this->response->redirect($url, $statusCode);
    }

    protected function setFlash(string $type, string $message): void
    {
        Session::setFlash($type, $message);
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
            exit;
        }
    }

    protected function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        $currentRole = Auth::role();
        if (!in_array($currentRole, $roles, true)) {
            $this->redirect('/dashboard');
            exit;
        }
    }
}
