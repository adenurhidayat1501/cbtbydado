<?php

namespace App\Controllers\Auth;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class PasswordController extends Controller
{
    private User $userModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
    }

    public function changePassword(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        $currentPassword = (string)$this->request->input('current_password', '');
        $newPassword = (string)$this->request->input('new_password', '');
        $confirmPassword = (string)$this->request->input('confirm_password', '');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->setFlash('error', 'Semua kolom password wajib diisi.');
            $this->redirect($this->getBackUrl());
            return;
        }

        if (strlen($newPassword) < 6) {
            $this->setFlash('error', 'Password baru minimal harus terdiri dari 6 karakter.');
            $this->redirect($this->getBackUrl());
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'Konfirmasi password baru tidak cocok.');
            $this->redirect($this->getBackUrl());
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $this->setFlash('error', 'Password saat ini yang Anda masukkan salah.');
            $this->redirect($this->getBackUrl());
            return;
        }

        // Hash new password using BCrypt
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->userModel->updatePassword($userId, $hash);

        // PRD Requirement: Auto-Logout when password is changed
        Auth::onPasswordChanged($userId);

        $this->setFlash('success', 'Password berhasil diperbarui! Demi keamanan, silakan masuk kembali dengan password baru Anda.');
        $isStudent = ($user['role'] ?? '') === 'siswa';
        $this->redirect($isStudent ? '/cbt/login' : '/login');
    }

    private function getBackUrl(): string
    {
        $role = Auth::role();
        return match ($role) {
            'admin' => '/dashboard',
            'guru' => '/guru/dashboard',
            'siswa' => '/student/dashboard',
            default => '/login',
        };
    }
}

