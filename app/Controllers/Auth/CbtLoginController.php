<?php

namespace App\Controllers\Auth;

use App\Config\Auth as AuthConfig;
use App\Core\Auth;
use App\Core\Controller;
use App\Models\MasterSiswa;
use App\Models\User;

class CbtLoginController extends Controller
{
    private User $userModel;
    private MasterSiswa $siswaModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
        $this->siswaModel = new MasterSiswa();
    }

    public function showLogin(): void
    {
        if (Auth::check() && Auth::role() === AuthConfig::ROLE_SISWA) {
            $this->redirect('/student/dashboard');
            return;
        }

        $this->render('auth/cbt_login', [
            'title' => 'Login Ujian CBT — DADO CBT',
            'error' => get_flash('error'),
            'success' => get_flash('success'),
        ], '');
    }

    public function login(): void
    {
        $identifier = trim((string)$this->request->input('identifier', ''));
        $password = trim((string)$this->request->input('password', ''));

        if (empty($identifier) || empty($password)) {
            $this->setFlash('error', 'Nomor Peserta / NIS / Username dan Password wajib diisi.');
            $this->redirect('/cbt/login');
            return;
        }

        // Try finding user by username first, or by NIS / NISN via master_siswa
        $user = $this->userModel->findByUsername($identifier);

        if (!$user) {
            $siswa = $this->siswaModel->findByNis($identifier);
            if (!$siswa) {
                $siswa = $this->siswaModel->findByNisn($identifier);
            }
            if ($siswa && !empty($siswa['id_user'])) {
                $user = $this->userModel->find($siswa['id_user']);
            }
        }

        if (!$user || $user['role'] !== AuthConfig::ROLE_SISWA) {
            $this->setFlash('error', 'Data peserta ujian tidak ditemukan atau bukan akun siswa.');
            $this->redirect('/cbt/login');
            return;
        }

        if (!(int)$user['active']) {
            $this->setFlash('error', 'Status akun siswa nonaktif. Hubungi Proktor CBT Sekolah.');
            $this->redirect('/cbt/login');
            return;
        }

        if (!password_verify($password, $user['password'])) {
            $this->setFlash('error', 'Password peserta yang Anda masukkan keliru.');
            $this->redirect('/cbt/login');
            return;
        }

        $profile = $this->siswaModel->findByUserId((int)$user['id']);

        // Authenticate with Single-Device Lock
        Auth::login($user, $profile);

        $this->setFlash('success', 'Selamat datang di Ruang Ujian CBT, ' . ($profile['nama'] ?? $user['username']));
        $this->redirect('/student/dashboard');
    }
}

