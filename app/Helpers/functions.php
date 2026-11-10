<?php

use App\Config\App;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
        } else {
            $baseUrl = App::get('url', 'http://localhost:8080');
        }
        if (empty($path)) {
            return $baseUrl;
        }
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path = ''): string
    {
        return base_url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('upload_url')) {
    function upload_url(string $path = ''): string
    {
        return base_url('uploads/' . ltrim($path, '/'));
    }
}

if (!function_exists('escape_html')) {
    function escape_html(?string $str): string
    {
        if ($str === null) {
            return '';
        }
        return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('safe_print_str')) {
    /**
     * Defensive escaping for printing cards, berita acara, and reports.
     * Prevents broken javascript/html when names contain ' or "
     */
    function safe_print_str(?string $str): string
    {
        if ($str === null) {
            return '';
        }
        return str_replace(['\\', "'", '"'], ['\\\\', '&#039;', '&quot;'], $str);
    }
}

if (!function_exists('format_indo_date')) {
    /**
     * Format date to Indonesian language string: e.g. "Senin, 10 November 2026" or "10 November 2026"
     */
    function format_indo_date(?string $datetime, bool $include_time = false, bool $include_day = false): string
    {
        if (empty($datetime) || $datetime === '0000-00-00' || $datetime === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return '-';
        }

        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $dayName = $days[date('l', $timestamp)] ?? '';
        $dayNum = date('j', $timestamp);
        $monthNum = (int)date('n', $timestamp);
        $monthName = $months[$monthNum] ?? '';
        $year = date('Y', $timestamp);

        $result = '';
        if ($include_day && !empty($dayName)) {
            $result .= $dayName . ', ';
        }
        $result .= "{$dayNum} {$monthName} {$year}";

        if ($include_time) {
            $result .= ' ' . date('H:i', $timestamp) . ' WIB';
        }

        return $result;
    }
}

if (!function_exists('fisher_yates_shuffle')) {
    /**
     * Cryptographically fair Fisher-Yates shuffle algorithm
     */
    function fisher_yates_shuffle(array &$array): bool
    {
        $count = count($array);
        if ($count <= 1) {
            return true;
        }

        for ($i = $count - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $temp = $array[$i];
            $array[$i] = $array[$j];
            $array[$j] = $temp;
        }

        return true;
    }
}

if (!function_exists('shuffled_array')) {
    /**
     * Returns a new array shuffled using Fisher-Yates
     */
    function shuffled_array(array $array): array
    {
        $copy = array_values($array);
        fisher_yates_shuffle($copy);
        return $copy;
    }
}

if (!function_exists('active_tp')) {
    /**
     * Get active academic year (Tahun Pelajaran)
     */
    function active_tp(): ?array
    {
        static $cachedTp = null;

        // Check if user session has a chosen TP override (for admin browsing)
        if (Session::has('active_tp_id')) {
            $sessionTpId = (int)Session::get('active_tp_id');
            if ($cachedTp !== null && (int)($cachedTp['id_tp'] ?? 0) === $sessionTpId) {
                return $cachedTp;
            }
            try {
                $db = Database::getInstance()->getPdo();
                $stmt = $db->prepare("SELECT * FROM master_tp WHERE id_tp = ? LIMIT 1");
                $stmt->execute([$sessionTpId]);
                $res = $stmt->fetch();
                if ($res) {
                    $cachedTp = $res;
                    return $cachedTp;
                }
            } catch (\Throwable $e) {
                // fall through to default active
            }
        }

        if ($cachedTp !== null) {
            return $cachedTp;
        }

        try {
            $db = Database::getInstance()->getPdo();
            $stmt = $db->query("SELECT * FROM master_tp WHERE active = 1 LIMIT 1");
            $cachedTp = $stmt->fetch() ?: null;
        } catch (\Throwable $e) {
            $cachedTp = null;
        }

        return $cachedTp;
    }
}

if (!function_exists('active_smt')) {
    /**
     * Get active semester (1=Ganjil, 2=Genap)
     */
    function active_smt(): ?array
    {
        static $cachedSmt = null;

        // Check if user session has a chosen SMT override
        if (Session::has('active_smt_id')) {
            $sessionSmtId = (int)Session::get('active_smt_id');
            if ($cachedSmt !== null && (int)($cachedSmt['id_smt'] ?? 0) === $sessionSmtId) {
                return $cachedSmt;
            }
            try {
                $db = Database::getInstance()->getPdo();
                $stmt = $db->prepare("SELECT * FROM master_smt WHERE id_smt = ? LIMIT 1");
                $stmt->execute([$sessionSmtId]);
                $res = $stmt->fetch();
                if ($res) {
                    $cachedSmt = $res;
                    return $cachedSmt;
                }
            } catch (\Throwable $e) {
                // fall through to default active
            }
        }

        if ($cachedSmt !== null) {
            return $cachedSmt;
        }

        try {
            $db = Database::getInstance()->getPdo();
            $stmt = $db->query("SELECT * FROM master_smt WHERE active = 1 LIMIT 1");
            $cachedSmt = $stmt->fetch() ?: null;
        } catch (\Throwable $e) {
            $cachedSmt = null;
        }

        return $cachedSmt;
    }
}

if (!function_exists('setting_sekolah')) {
    /**
     * Get school profile record
     */
    function setting_sekolah(): ?array
    {
        static $cachedSchool = null;
        if ($cachedSchool !== null) {
            return $cachedSchool;
        }

        try {
            $db = Database::getInstance()->getPdo();
            $stmt = $db->query("SELECT * FROM setting_sekolah ORDER BY id_setting ASC LIMIT 1");
            $cachedSchool = $stmt->fetch() ?: null;
        } catch (\Throwable $e) {
            $cachedSchool = null;
        }

        return $cachedSchool;
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('auth_check')) {
    function auth_check(): bool
    {
        return Auth::check();
    }
}

if (!function_exists('auth_role')) {
    function auth_role(): ?string
    {
        return Auth::role();
    }
}

if (!function_exists('auth_id')) {
    function auth_id(): ?int
    {
        return Auth::id();
    }
}

if (!function_exists('set_flash')) {
    function set_flash(string $type, string $message): void
    {
        Session::setFlash($type, $message);
    }
}

if (!function_exists('get_flash')) {
    function get_flash(string $type): ?string
    {
        return Session::getFlash($type);
    }
}

if (!function_exists('has_flash')) {
    function has_flash(string $type): bool
    {
        return Session::hasFlash($type);
    }
}

if (!function_exists('auth_guru')) {
    function auth_guru(): ?array
    {
        $user = auth_user();
        if ($user && ($user['role'] ?? '') === 'guru') {
            return $user['profile'] ?? null;
        }
        return null;
    }
}

if (!function_exists('auth_siswa')) {
    function auth_siswa(): ?array
    {
        $user = auth_user();
        if ($user && ($user['role'] ?? '') === 'siswa') {
            return $user['profile'] ?? null;
        }
        return null;
    }
}


