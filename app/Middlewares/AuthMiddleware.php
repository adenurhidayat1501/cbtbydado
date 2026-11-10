<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request, Response $response, callable $next)
    {
        if (!Auth::check()) {
            Session::setFlash('error', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.');
            $redirectUrl = str_starts_with($request->getUri(), '/student') || str_starts_with($request->getUri(), '/cbt/exam')
                ? '/cbt/login'
                : '/login';
            $response->redirect($redirectUrl);
            return;
        }

        // Enforce Single-Device Session Lock & active check
        if (!Auth::verifySessionToken()) {
            $isStudent = Auth::role() === 'siswa';
            Auth::logout();
            Session::setFlash(
                'error',
                'Sesi akun Anda telah diakhiri karena login di perangkat lain atau telah di-reset oleh proktor/pengawas.'
            );
            $redirectUrl = $isStudent ? '/cbt/login' : '/login';
            $response->redirect($redirectUrl);
            return;
        }

        return $next();
    }
}

