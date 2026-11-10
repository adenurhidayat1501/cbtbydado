<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array|string $roles = [])
    {
        if (is_string($roles)) {
            $this->allowedRoles = explode(',', $roles);
        } else {
            $this->allowedRoles = $roles;
        }
        $this->allowedRoles = array_map('trim', $this->allowedRoles);
    }

    public function handle(Request $request, Response $response, callable $next)
    {
        $currentRole = Auth::role();

        if (!$currentRole || (!empty($this->allowedRoles) && !in_array($currentRole, $this->allowedRoles, true))) {
            $response->setStatusCode(403);
            $errorView = __DIR__ . '/../Views/errors/403.php';
            if (file_exists($errorView)) {
                extract([
                    'title' => '403 - Akses Ditolak',
                    'message' => 'Anda tidak memiliki hak akses untuk membuka halaman ini.'
                ]);
                require $errorView;
            } else {
                echo "<h1>403 Akses Ditolak</h1><p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>";
            }
            exit;
        }

        return $next();
    }
}

