<?php

namespace App\Controllers\Auth;

use App\Config\Auth as AuthConfig;
use App\Core\Auth;
use App\Core\Controller;
use App\Models\MasterGuru;
use App\Models\User;

class LoginController extends Controller
{
    private User $userModel;
    private MasterGuru $guruModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
        $this->guruModel = new MasterGuru();
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect(AuthConfig::getRoleRedirect(Auth::role() ?: 'siswa'));
            return;
        }

        $this->render('auth/login', [
            'title' => 'Login Portal — DADO CBT',
            'error' => $this->request->input('error') ?? get_flash('error'),
            'success' => get_flash('success'),
        ], '');
    }

    public function login(): void
    {
        $username = trim((string)$this->request->input('username', ''));
        $password = trim((string)$this->request->input('password', ''));

        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'Username dan password wajib diisi.');
            $this->redirect('/login');
            return;
        }

        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            $this->setFlash('error', 'Username atau password yang Anda masukkan salah.');
            $this->redirect('/login');
            return;
        }

        if (!(int)$user['active']) {
            $this->setFlash('error', 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.');
            $this->redirect('/login');
            return;
        }

        // Check if student tries to login through regular portal
        if ($user['role'] === AuthConfig::ROLE_SISWA) {
            $this->setFlash('error', 'Akun peserta didik harus masuk melalui Portal CBT Siswa.');
            $this->redirect('/cbt/login');
            return;
        }

        // Verify password hash
        if (!password_verify($password, $user['password'])) {
            $this->setFlash('error', 'Username atau password yang Anda masukkan salah.');
            $this->redirect('/login');
            return;
        }

        // Retrieve profile if Guru
        $profile = null;
        if ($user['role'] === AuthConfig::ROLE_GURU) {
            $profile = $this->guruModel->findByUserId((int)$user['id']);
        }

        // Perform login with Single-Device token generation
        Auth::login($user, $profile);

        $this->setFlash('success', 'Selamat datang kembali, ' . ($user['username'] ?? 'User') . '!');
        $redirectUrl = AuthConfig::getRoleRedirect($user['role']);
        $this->redirect($redirectUrl);
    }

    public function logout(): void
    {
        $isStudent = Auth::role() === 'siswa';
        Auth::logout();
        $this->setFlash('success', 'Anda telah berhasil keluar dari sistem.');
        $this->redirect($isStudent ? '/cbt/login' : '/login');
    }
}

