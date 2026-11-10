<?php

/**
 * ==============================================================================
 * DADO CBT v1.5.3 — GITHUB WEBHOOK AUTO UPDATE LISTENER
 * ==============================================================================
 * Endpoint penerima webhook otomatis saat ada push baru ke repository GitHub.
 * Dilindungi enkripsi HMAC-SHA256 (X-Hub-Signature-256).
 *
 * URL Webhook GitHub: https://domain-anda.com/webhook.php
 * Content Type      : application/json
 * Secret            : Nilai dari GITHUB_WEBHOOK_SECRET di .env
 * Event             : Just the push event
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$baseDir = dirname(__DIR__);
$envFile = $baseDir . '/.env';
$logDir = $baseDir . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
$logFile = $logDir . '/webhook.log';

function logWebhook(string $msg, string $logFile): void
{
    $entry = "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n";
    @file_put_contents($logFile, $entry, FILE_APPEND);
}

// 1. Validasi Metode Request
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Hanya metode POST yang diizinkan']);
    exit;
}

// 2. Baca Konfigurasi .env
$webhookSecret = '';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (str_starts_with($line, 'GITHUB_WEBHOOK_SECRET=')) {
            $parts = explode('=', $line, 2);
            $webhookSecret = trim($parts[1] ?? '', " \t\n\r\0\x0B\"'");
            break;
        }
    }
}

// 3. Baca Payload Mentah
$rawPayload = file_get_contents('php://input');
if (empty($rawPayload)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Payload kosong']);
    exit;
}

// 4. Verifikasi Tanda Tangan GitHub (HMAC-SHA256)
$githubSignature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (!empty($webhookSecret)) {
    if (empty($githubSignature)) {
        http_response_code(401);
        logWebhook("DITOLAK: Header X-Hub-Signature-256 tidak disertakan.", $logFile);
        echo json_encode(['status' => 'error', 'message' => 'Header X-Hub-Signature-256 wajib disertakan']);
        exit;
    }

    $expectedSignature = 'sha256=' . hash_hmac('sha256', $rawPayload, $webhookSecret);
    if (!hash_equals($expectedSignature, $githubSignature)) {
        http_response_code(403);
        logWebhook("DITOLAK: Signature HMAC-SHA256 tidak cocok.", $logFile);
        echo json_encode(['status' => 'error', 'message' => 'Tanda tangan otentikasi GitHub tidak valid']);
        exit;
    }
}

// 5. Cek Jenis Event GitHub
$githubEvent = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
if ($githubEvent === 'ping') {
    logWebhook("PING diterima dari GitHub. Koneksi Webhook Aktif & Valid.", $logFile);
    echo json_encode(['status' => 'success', 'message' => 'Pong! Webhook DADO CBT aktif & terverifikasi.']);
    exit;
}

if ($githubEvent !== 'push') {
    logWebhook("Event diabaikan: {$githubEvent}", $logFile);
    echo json_encode(['status' => 'ignored', 'message' => "Event '{$githubEvent}' diabaikan (hanya memproses 'push')"]);
    exit;
}

// 6. Parsing Data Push
$payload = json_decode($rawPayload, true);
$ref = $payload['ref'] ?? '';
$targetBranch = 'refs/heads/main';

if ($ref !== $targetBranch && $ref !== 'refs/heads/master') {
    logWebhook("Push diabaikan: bukan target branch ({$ref})", $logFile);
    echo json_encode(['status' => 'ignored', 'message' => "Push ke {$ref} diabaikan"]);
    exit;
}

$commitId = substr($payload['after'] ?? 'unknown', 0, 7);
$pusher = $payload['pusher']['name'] ?? 'Unknown Pusher';
logWebhook("Push terdeteksi dari {$pusher} (Commit: {$commitId} ke {$ref}). Memulai update...", $logFile);

// 7. Eksekusi Script Pembaruan di Background
$updateScript = $baseDir . '/deploy/update.sh';
if (file_exists($updateScript)) {
    $cmd = "sudo " . escapeshellarg($updateScript) . " >> " . escapeshellarg($logFile) . " 2>&1 &";
    exec($cmd);

    echo json_encode([
        'status' => 'success',
        'message' => 'Auto Update GitHub sedang berjalan di latar belakang.',
        'commit' => $commitId,
        'pusher' => $pusher,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    http_response_code(500);
    logWebhook("ERROR: Script update {$updateScript} tidak ditemukan.", $logFile);
    echo json_encode(['status' => 'error', 'message' => 'Script update tidak ditemukan']);
}

