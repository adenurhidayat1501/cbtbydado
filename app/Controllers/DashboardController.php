<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\CbtToken;

class DashboardController extends Controller
{
    public function index(): void
    {
        $role = Auth::role();
        if ($role === 'guru') {
            $this->redirect('/guru/dashboard');
            return;
        } elseif ($role === 'siswa') {
            $this->redirect('/student/dashboard');
            return;
        }

        $db = Database::getInstance()->getPdo();
        $tp = active_tp();
        $smt = active_smt();
        $tpId = (int)($tp['id_tp'] ?? 0);
        $smtId = (int)($smt['id_smt'] ?? 0);

        // Fetch dashboard statistics
        $stats = [
            'total_siswa' => 0,
            'total_guru' => 0,
            'total_kelas' => 0,
            'total_bank' => 0,
            'total_jadwal_hari_ini' => 0,
        ];

        try {
            $stats['total_siswa'] = (int)$db->query("SELECT COUNT(*) FROM master_siswa WHERE status = 1")->fetchColumn();
            $stats['total_guru'] = (int)$db->query("SELECT COUNT(*) FROM master_guru WHERE status = 1")->fetchColumn();

            $stmtKelas = $db->prepare("SELECT COUNT(*) FROM master_kelas WHERE id_tp = ? AND id_smt = ?");
            $stmtKelas->execute([$tpId, $smtId]);
            $stats['total_kelas'] = (int)$stmtKelas->fetchColumn();

            $stmtBank = $db->prepare("SELECT COUNT(*) FROM cbt_bank_soal WHERE id_tp = ? AND id_smt = ?");
            $stmtBank->execute([$tpId, $smtId]);
            $stats['total_bank'] = (int)$stmtBank->fetchColumn();

            $stmtJadwal = $db->prepare("SELECT COUNT(*) FROM cbt_jadwal WHERE id_tp = ? AND id_smt = ? AND status = 1 AND CURDATE() BETWEEN tgl_mulai AND tgl_selesai");
            $stmtJadwal->execute([$tpId, $smtId]);
            $stats['total_jadwal_hari_ini'] = (int)$stmtJadwal->fetchColumn();

            // Fetch running text announcements
            $stmtAnnounce = $db->query("SELECT * FROM running_text WHERE active = 1 AND tujuan IN ('admin', 'semua') ORDER BY id_text DESC");
            $announcements = $stmtAnnounce->fetchAll();

            // Fetch active token
            $tokenModel = new CbtToken();
            $tokenData = $tokenModel->getActiveToken();
        } catch (\Throwable $e) {
            $announcements = [];
            $tokenData = null;
        }

        $this->render('dashboard/index', [
            'title' => 'Dashboard Utama — DADO CBT',
            'stats' => $stats,
            'announcements' => $announcements,
            'tokenData' => $tokenData,
            'tp' => $tp,
            'smt' => $smt,
            'school' => setting_sekolah(),
        ]);
    }

    public function generateToken(): void
    {
        $tokenModel = new CbtToken();
        $newToken = $tokenModel->generateNewToken(6);

        if ($this->request->isAjax()) {
            $this->json([
                'success' => true,
                'token' => $newToken,
                'updated_at' => format_indo_date(date('Y-m-d H:i:s'), true),
                'message' => "Token CBT baru berhasil di-generate: {$newToken}"
            ]);
            return;
        }

        $this->setFlash('success', "Token CBT baru berhasil di-generate: {$newToken}");
        $this->redirect('/dashboard');
    }

    public function guruDashboard(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $userId = Auth::id();

        $db = Database::getInstance()->getPdo();
        $stmtGuru = $db->prepare("SELECT * FROM master_guru WHERE id_user = ? LIMIT 1");
        $stmtGuru->execute([$userId]);
        $guru = $stmtGuru->fetch();

        $this->render('dashboard/guru', [
            'title' => 'Dashboard Pendidik — DADO CBT',
            'guru' => $guru,
            'tp' => $tp,
            'smt' => $smt,
            'school' => setting_sekolah(),
        ]);
    }

    public function studentDashboard(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $userId = Auth::id();

        $db = Database::getInstance()->getPdo();
        $stmtSiswa = $db->prepare("SELECT s.*, k.nama_kelas FROM master_siswa s LEFT JOIN kelas_siswa ks ON ks.id_siswa = s.id_siswa LEFT JOIN master_kelas k ON k.id_kelas = ks.id_kelas WHERE s.id_user = ? LIMIT 1");
        $stmtSiswa->execute([$userId]);
        $siswa = $stmtSiswa->fetch();

        // Fetch active exams today
        $exams = [];
        try {
            $stmtExam = $db->prepare("
                SELECT j.*, b.bank_kode, m.nama_mapel, jenis.nama_jenis,
                       IFNULL(d.status, 0) as status_durasi,
                       d.mulai as mulai_ujian, d.selesai as selesai_ujian
                FROM cbt_jadwal j
                JOIN cbt_bank_soal b ON b.id_bank = j.id_bank
                JOIN master_mapel m ON m.id_mapel = b.bank_mapel_id
                JOIN cbt_jenis jenis ON jenis.id_jenis = j.id_jenis
                LEFT JOIN cbt_durasi_siswa d ON d.id_jadwal = j.id_jadwal AND d.id_siswa = ?
                WHERE j.status = 1 AND CURDATE() BETWEEN j.tgl_mulai AND j.tgl_selesai
                ORDER BY j.jam_ke ASC, j.tgl_mulai ASC
            ");
            $stmtExam->execute([$siswa['id_siswa'] ?? 0]);
            $exams = $stmtExam->fetchAll();
        } catch (\Throwable $e) {
            $exams = [];
        }

        $this->render('dashboard/siswa', [
            'title' => 'Ruang Asesmen Siswa — DADO CBT',
            'siswa' => $siswa,
            'exams' => $exams,
            'tp' => $tp,
            'smt' => $smt,
            'school' => setting_sekolah(),
        ], 'layouts/master');
    }
}
