<?php

namespace App\Core;

class Auth
{
    private static ?array $cachedUser = null;

    public static function check(): bool
    {
        Session::start();
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        Session::start();
        return Session::get('user_id');
    }

    public static function role(): ?string
    {
        Session::start();
        return Session::get('role');
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $userId = self::id();
        $db = Database::getInstance()->getPdo();

        $stmt = $db->prepare("SELECT id, username, email, role, session_token, active, updated_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !(int)$user['active']) {
            self::logout();
            return null;
        }

        // Attach profile data based on role
        if ($user['role'] === 'guru') {
            $stmtGuru = $db->prepare("SELECT * FROM master_guru WHERE id_user = ? LIMIT 1");
            $stmtGuru->execute([$userId]);
            $user['profile'] = $stmtGuru->fetch() ?: [];
            $user['display_name'] = $user['profile']['nama_guru'] ?? $user['username'];
            $user['foto'] = $user['profile']['foto'] ?? null;
        } elseif ($user['role'] === 'siswa') {
            $stmtSiswa = $db->prepare("SELECT * FROM master_siswa WHERE id_user = ? LIMIT 1");
            $stmtSiswa->execute([$userId]);
            $user['profile'] = $stmtSiswa->fetch() ?: [];
            $user['display_name'] = $user['profile']['nama'] ?? $user['username'];
            $user['foto'] = $user['profile']['foto'] ?? null;
        } else {
            $user['display_name'] = 'Administrator CBT';
            $user['foto'] = null;
        }

        self::$cachedUser = $user;
        return self::$cachedUser;
    }

    /**
     * Authenticate and initialize a session with Single-Device Lock token
     */
    public static function login(array $userRow, ?array $profileRow = null): void
    {
        Session::start();
        Session::regenerate(true);

        $sessionToken = bin2hex(random_bytes(24));
        $userId = (int)$userRow['id'];

        // Persist session token into users table
        $db = Database::getInstance()->getPdo();
        $stmt = $db->prepare("UPDATE users SET session_token = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$sessionToken, $userId]);

        Session::set('user_id', $userId);
        Session::set('username', $userRow['username']);
        Session::set('role', $userRow['role']);
        Session::set('session_token', $sessionToken);

        if ($userRow['role'] === 'guru' && $profileRow) {
            Session::set('nama', $profileRow['nama_guru'] ?? $userRow['username']);
            Session::set('foto', $profileRow['foto'] ?? null);
            Session::set('id_guru', $profileRow['id_guru'] ?? null);
        } elseif ($userRow['role'] === 'siswa' && $profileRow) {
            Session::set('nama', $profileRow['nama'] ?? $userRow['username']);
            Session::set('foto', $profileRow['foto'] ?? null);
            Session::set('id_siswa', $profileRow['id_siswa'] ?? null);
            Session::set('nis', $profileRow['nis'] ?? null);
        } else {
            Session::set('nama', 'Administrator CBT');
            Session::set('foto', null);
        }

        self::$cachedUser = null;
    }

    /**
     * Verify that the active session matches database session_token.
     * Enforces Single-Device Lock and checks for proctor/admin reset.
     */
    public static function verifySessionToken(): bool
    {
        if (!self::check()) {
            return false;
        }

        $userId = self::id();
        $localToken = Session::get('session_token');

        if (empty($localToken)) {
            return false;
        }

        $db = Database::getInstance()->getPdo();
        $stmt = $db->prepare("SELECT session_token, active FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!$row || !(int)$row['active']) {
            return false;
        }

        // If database token doesn't match local session token, account was logged in elsewhere or reset
        if ($row['session_token'] !== $localToken) {
            return false;
        }

        return true;
    }

    /**
     * Auto-logout on password change
     */
    public static function onPasswordChanged(int $userId): void
    {
        $db = Database::getInstance()->getPdo();
        $newToken = bin2hex(random_bytes(24));
        $stmt = $db->prepare("UPDATE users SET session_token = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newToken, $userId]);

        // Destroy current session to trigger re-login
        Session::destroy();
        self::$cachedUser = null;
    }

    public static function logout(): void
    {
        if (self::check()) {
            $userId = self::id();
            try {
                $db = Database::getInstance()->getPdo();
                $stmt = $db->prepare("UPDATE users SET session_token = NULL WHERE id = ?");
                $stmt->execute([$userId]);
            } catch (\Throwable $e) {
                // Ignore DB error during logout
            }
        }

        Session::destroy();
        self::$cachedUser = null;
    }
}

