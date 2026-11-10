<?php

namespace App\Config;

class Auth
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_GURU = 'guru';
    public const ROLE_SISWA = 'siswa';

    // Guru sub-roles in jabatan_guru
    public const JABATAN_KEPSEK = 1;
    public const JABATAN_WALI = 2;
    public const JABATAN_MAPEL = 3;
    public const JABATAN_PENGAWAS = 4;

    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_GURU => 'Guru / Pendidik',
            self::ROLE_SISWA => 'Peserta Didik',
        ];
    }

    public static function getRoleRedirect(string $role): string
    {
        return match ($role) {
            self::ROLE_ADMIN => '/dashboard',
            self::ROLE_GURU => '/guru/dashboard',
            self::ROLE_SISWA => '/student/dashboard',
            default => '/login',
        };
    }
}

