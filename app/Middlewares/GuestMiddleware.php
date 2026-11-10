<?php

namespace App\Middlewares;

use App\Config\Auth as AuthConfig;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class GuestMiddleware
{
    public function handle(Request $request, Response $response, callable $next)
    {
        if (Auth::check() && Auth::verifySessionToken()) {
            $role = Auth::role();
            $target = AuthConfig::getRoleRedirect($role ?: 'siswa');
            $response->redirect($target);
            return;
        }

        return $next();
    }
}

