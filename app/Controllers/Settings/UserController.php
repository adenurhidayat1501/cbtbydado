<?php

namespace App\Controllers\Settings;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class UserController extends Controller
{
    private User $userModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
    }

    public function index(): void
    {
        $roleFilter = $this->request->input('role');
        $statusFilter = $this->request->input('status');

        $sql = "SELECT u.*, 
                       CASE 
                           WHEN u.role = 'guru' THEN g.nama_guru 
                           WHEN u.role = 'siswa' THEN s.nama 
                           ELSE 'Administrator' 
                       END as display_name
                FROM users u
                LEFT JOIN master_guru g ON g.id_user = u.id
                LEFT JOIN master_siswa s ON s.id_user = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($roleFilter)) {
            $sql .= " AND u.role = ?";
            $params[] = $roleFilter;
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $sql .= " AND u.active = ?";
            $params[] = (int)$statusFilter;
        }

        $sql .= " ORDER BY u.role ASC, u.id DESC";
        $users = $this->userModel->query($sql, $params);

        $this->render('settings/users', [
            'title' => 'Manajemen Pengguna — DADO CBT',
            'users' => $users,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function storeAdmin(): void
    {
        $username = trim((string)$this->request->input('username', ''));
        $password = (string)$this->request->input('password', '');
        $email = trim((string)$this->request->input('email', ''));

        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'Username dan Password admin wajib diisi.');
            $this->redirect('/settings/users');
            return;
        }

        $exist = $this->userModel->findByUsername($username);
        if ($exist) {
            $this->setFlash('error', "Username {$username} sudah digunakan.");
            $this->redirect('/settings/users');
            return;
        }

        $this->userModel->insert([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'email' => !empty($email) ? $email : null,
            'role' => 'admin',
            'active' => 1,
        ]);

        $this->setFlash('success', "Akun Administrator {$username} berhasil dibuat.");
        $this->redirect('/settings/users');
    }

    public function resetPassword(string $id): void
    {
        $idUser = (int)$id;
        $user = $this->userModel->find($idUser);

        if (!$user) {
            $this->setFlash('error', 'Pengguna tidak ditemukan.');
            $this->redirect('/settings/users');
            return;
        }

        $defaultPass = match ($user['role']) {
            'admin' => 'admin123',
            'guru' => 'guru123',
            'siswa' => 'siswa123',
            default => '123456',
        };

        $hash = password_hash($defaultPass, PASSWORD_BCRYPT);
        $this->userModel->updatePassword($idUser, $hash);

        // Invalidate active session for single-device security
        Auth::onPasswordChanged($idUser);

        $this->setFlash('success', "Password akun {$user['username']} berhasil di-reset ke default: {$defaultPass}");
        $this->redirect('/settings/users');
    }

    public function toggleStatus(string $id): void
    {
        $idUser = (int)$id;
        $user = $this->userModel->find($idUser);

        if (!$user) {
            $this->setFlash('error', 'Pengguna tidak ditemukan.');
            $this->redirect('/settings/users');
            return;
        }

        if ($idUser === (int)Auth::id()) {
            $this->setFlash('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            $this->redirect('/settings/users');
            return;
        }

        $newStatus = (int)$user['active'] === 1 ? 0 : 1;
        $this->userModel->update($idUser, ['active' => $newStatus]);

        $statusText = $newStatus === 1 ? 'diaktifkan' : 'dinonaktifkan';
        $this->setFlash('success', "Akun {$user['username']} berhasil {$statusText}.");
        $this->redirect('/settings/users');
    }

    public function delete(string $id): void
    {
        $idUser = (int)$id;
        if ($idUser === (int)Auth::id()) {
            $this->setFlash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            $this->redirect('/settings/users');
            return;
        }

        $this->userModel->delete($idUser);
        $this->setFlash('success', 'Akun pengguna berhasil dihapus.');
        $this->redirect('/settings/users');
    }
}

