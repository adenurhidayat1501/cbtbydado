<?php

namespace App\Services;

use App\Core\Database;
use App\Models\RaporAdminSetting;
use App\Models\RaporKkm;
use App\Models\RaporKikd;
use App\Models\RaporNilaiHarian;
use App\Models\RaporNilaiPts;
use App\Models\RaporNilaiAkhir;
use App\Models\RaporCatatanWali;
use App\Models\RaporNaik;
use PDO;

class RaporCalculatorService
{
    protected PDO $db;
    protected RaporAdminSetting $adminSettingModel;
    protected RaporKkm $kkmModel;
    protected RaporKikd $kikdModel;
    protected RaporNilaiHarian $harianModel;
    protected RaporNilaiPts $ptsModel;
    protected RaporNilaiAkhir $akhirModel;

    public function __construct()
    {
        $this->db = Database::getInstance()->getPdo();
        $this->adminSettingModel = new RaporAdminSetting();
        $this->kkmModel = new RaporKkm();
        $this->kikdModel = new RaporKikd();
        $this->harianModel = new RaporNilaiHarian();
        $this->ptsModel = new RaporNilaiPts();
        $this->akhirModel = new RaporNilaiAkhir();
    }

    /**
     * Menghitung Predikat (A, B, C, D) berdasarkan nilai dan KKM
     * Rumus Interval: (100 - KKM) / 3
     */
    public function calculatePredicate(float $score, float $kkm = 75.0): string
    {
        if ($score <= 0) {
            return '-';
        }

        $kkm = max(1.0, min(99.0, $kkm));
        $interval = (100.0 - $kkm) / 3.0;

        $batasB = 100.0 - $interval;
        $batasC = 100.0 - (2.0 * $interval);

        if ($score >= $batasB) {
            return 'A';
        } elseif ($score >= $batasC) {
            return 'B';
        } elseif ($score >= $kkm) {
            return 'C';
        } else {
            return 'D';
        }
    }

    /**
     * Menghitung Nilai Akhir (NA) dari PH, PTS, dan PAS berdasarkan bobot
     */
    public function calculateFinalScore(float $ph, ?float $pts, ?float $pas, array $weights): float
    {
        $wPh = (float)($weights['bobot_ph'] ?? 50);
        $wPts = (float)($weights['bobot_pts'] ?? 25);
        $wPas = (float)($weights['bobot_pas'] ?? 25);

        // Fallback jika PTS / PAS belum ada
        $scorePts = $pts !== null ? $pts : $ph;
        $scorePas = $pas !== null ? $pas : $ph;

        $totalWeight = $wPh + $wPts + $wPas;
        if ($totalWeight <= 0) {
            $totalWeight = 100.0;
        }

        $totalScore = ($wPh * $ph) + ($wPts * $scorePts) + ($wPas * $scorePas);
        return round($totalScore / $totalWeight, 2);
    }

    /**
     * Generate deskripsi capaian kompetensi otomatis dari KIKD
     */
    public function generateAutoDescription(array $kikdList, array $scores, string $namaSiswa = ''): string
    {
        if (empty($kikdList)) {
            return "Menunjukkan pemahaman yang baik dalam pembelajaran.";
        }

        $maxKd = null;
        $minKd = null;
        $maxScore = -1.0;
        $minScore = 999.0;

        foreach ($kikdList as $kd) {
            $nomor = $kd['nomor_kikd'] ?? '';
            $isi = $kd['isi_kikd'] ?? '';
            $score = isset($scores[$nomor]) ? (float)$scores[$nomor] : 75.0;

            if ($score > $maxScore) {
                $maxScore = $score;
                $maxKd = $isi;
            }
            if ($score < $minScore) {
                $minScore = $score;
                $minKd = $isi;
            }
        }

        if ($maxKd && $minKd && $maxKd !== $minKd && ($maxScore - $minScore) >= 5) {
            return "Sangat menguasai kemampuan {$maxKd}, serta perlu bimbingan dan peningkatan dalam {$minKd}.";
        } elseif ($maxKd) {
            return "Memiliki pemahaman dan penguasaan yang sangat baik dalam {$maxKd}.";
        }

        return "Menunjukkan pemahaman yang baik terhadap materi pembelajaran.";
    }

    /**
     * Sinkronisasi nilai tugas E-Learning ke tabel rapor_nilai_harian
     */
    public function syncElearningGrades(int $idTp, int $idSmt, int $idKelas, int $idMapel): int
    {
        // Ambil seluruh tugas (jenis=2) untuk mapel & kelas terkait
        $stmtTugas = $this->db->prepare(
            "SELECT m.id_materi 
             FROM kelas_materi m
             JOIN kelas_jadwal_materi jm ON jm.id_materi = m.id_materi
             WHERE m.id_tp = ? AND m.id_smt = ? AND m.id_mapel = ? AND m.jenis = 2 AND jm.id_kelas = ?
             ORDER BY m.id_materi ASC"
        );
        $stmtTugas->execute([$idTp, $idSmt, $idMapel, $idKelas]);
        $tugasList = $stmtTugas->fetchAll();

        // Ambil seluruh siswa di kelas ini
        $stmtSiswa = $this->db->prepare(
            "SELECT s.id_siswa 
             FROM kelas_siswa ks
             JOIN master_siswa s ON ks.id_siswa = s.id_siswa
             WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?"
        );
        $stmtSiswa->execute([$idTp, $idSmt, $idKelas]);
        $siswaList = $stmtSiswa->fetchAll();

        $updatedCount = 0;
        $kkmSetting = $this->getKkmValue($idTp, $idSmt, $idKelas, $idMapel);

        foreach ($siswaList as $siswa) {
            $idSiswa = (int)$siswa['id_siswa'];

            // Hitung rata-rata nilai tugas di log_materi
            $stmtNilai = $this->db->prepare(
                "SELECT lm.nilai 
                 FROM log_materi lm
                 JOIN kelas_materi m ON lm.id_materi = m.id_materi
                 JOIN kelas_jadwal_materi jm ON jm.id_materi = m.id_materi
                 WHERE m.id_tp = ? AND m.id_smt = ? AND m.id_mapel = ? AND m.jenis = 2 
                   AND jm.id_kelas = ? AND lm.id_siswa = ? AND lm.nilai IS NOT NULL"
            );
            $stmtNilai->execute([$idTp, $idSmt, $idMapel, $idKelas, $idSiswa]);
            $scores = $stmtNilai->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($scores)) {
                $avg = round(array_sum($scores) / count($scores), 2);
                $predikat = $this->calculatePredicate($avg, (float)$kkmSetting['kkm']);

                $this->harianModel->saveEntry([
                    'id_tp' => $idTp,
                    'id_smt' => $idSmt,
                    'id_kelas' => $idKelas,
                    'id_mapel' => $idMapel,
                    'id_siswa' => $idSiswa,
                    'p_rata_rata' => $avg,
                    'p_predikat' => $predikat,
                    'k_rata_rata' => $avg,
                    'k_predikat' => $predikat
                ]);
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Sinkronisasi nilai CBT (PTS & PAS) ke rapor
     */
    public function syncCbtGrades(int $idTp, int $idSmt, int $idKelas, int $idMapel): array
    {
        $kkmSetting = $this->getKkmValue($idTp, $idSmt, $idKelas, $idMapel);
        $kkmVal = (float)$kkmSetting['kkm'];

        // Cari jadwal ujian yang sudah ada nilainya untuk mapel dan kelas ini
        $stmtJadwal = $this->db->prepare(
            "SELECT j.id_jadwal, j.id_jenis, cj.kode_jenis, j.bank_id
             FROM cbt_jadwal j
             JOIN cbt_bank_soal b ON j.bank_id = b.id_bank
             JOIN cbt_jenis cj ON j.id_jenis = cj.id_jenis
             WHERE b.id_tp = ? AND b.id_smt = ? AND b.id_mapel = ?
               AND (FIND_IN_SET(?, j.kelas_id) > 0 OR j.kelas_id = '' OR j.kelas_id IS NULL)"
        );
        $stmtJadwal->execute([$idTp, $idSmt, $idMapel, $idKelas]);
        $jadwalList = $stmtJadwal->fetchAll();

        $syncedPts = 0;
        $syncedPas = 0;

        foreach ($jadwalList as $j) {
            $idJadwal = (int)$j['id_jadwal'];
            $kodeJenis = strtoupper(trim($j['kode_jenis'] ?? ''));

            // Ambil seluruh nilai siswa untuk jadwal ini
            $stmtScores = $this->db->prepare(
                "SELECT n.id_siswa, COALESCE(n.skor_katrol, n.total_skor) AS final_score
                 FROM cbt_nilai n
                 JOIN kelas_siswa ks ON ks.id_siswa = n.id_siswa 
                      AND ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
                 WHERE n.id_jadwal = ?"
            );
            $stmtScores->execute([$idTp, $idSmt, $idKelas, $idJadwal]);
            $scores = $stmtScores->fetchAll();

            foreach ($scores as $s) {
                $idSiswa = (int)$s['id_siswa'];
                $val = (float)$s['final_score'];
                $pred = $this->calculatePredicate($val, $kkmVal);

                if (str_contains($kodeJenis, 'PTS') || str_contains($kodeJenis, 'MID')) {
                    $this->ptsModel->saveEntry([
                        'id_tp' => $idTp,
                        'id_smt' => $idSmt,
                        'id_kelas' => $idKelas,
                        'id_mapel' => $idMapel,
                        'id_siswa' => $idSiswa,
                        'nilai' => $val,
                        'predikat' => $pred
                    ]);
                    $syncedPts++;
                } else {
                    // PAS / PAT / Lainnya
                    // Hitung nilai akhir sekaligus
                    $harian = $this->harianModel->getSingle($idTp, $idSmt, $idKelas, $idMapel, $idSiswa);
                    $phVal = (float)($harian['p_rata_rata'] ?? $val);
                    $ptsRecord = $this->ptsModel->getSingle($idTp, $idSmt, $idKelas, $idMapel, $idSiswa);
                    $ptsVal = isset($ptsRecord['nilai']) ? (float)$ptsRecord['nilai'] : null;

                    $naVal = $this->calculateFinalScore($phVal, $ptsVal, $val, $kkmSetting);
                    $naPred = $this->calculatePredicate($naVal, $kkmVal);

                    $this->akhirModel->saveEntry([
                        'id_tp' => $idTp,
                        'id_smt' => $idSmt,
                        'id_kelas' => $idKelas,
                        'id_mapel' => $idMapel,
                        'id_siswa' => $idSiswa,
                        'nilai' => $val,
                        'akhir' => $naVal,
                        'predikat' => $naPred
                    ]);
                    $syncedPas++;
                }
            }
        }

        return ['pts' => $syncedPts, 'pas' => $syncedPas];
    }

    /**
     * Dapatkan nilai KKM dan bobot aktif (prioritas KKM khusus mapel, fallback ke KKM umum)
     */
    public function getKkmValue(int $idTp, int $idSmt, int $idKelas, int $idMapel): array
    {
        $adminSetting = $this->adminSettingModel->getSetting($idTp, $idSmt);
        if ((int)$adminSetting['kkm_tunggal'] === 1) {
            return [
                'kkm' => (int)$adminSetting['kkm'],
                'bobot_ph' => (int)$adminSetting['bobot_ph'],
                'bobot_pts' => (int)$adminSetting['bobot_pts'],
                'bobot_pas' => (int)$adminSetting['bobot_pas']
            ];
        }

        $mapelKkm = $this->kkmModel->getByClassAndMapel($idTp, $idSmt, $idKelas, $idMapel);
        if ($mapelKkm) {
            return [
                'kkm' => (int)$mapelKkm['kkm'],
                'bobot_ph' => (int)$mapelKkm['bobot_ph'],
                'bobot_pts' => (int)$mapelKkm['bobot_pts'],
                'bobot_pas' => (int)$mapelKkm['bobot_pas']
            ];
        }

        return [
            'kkm' => (int)$adminSetting['kkm'],
            'bobot_ph' => (int)$adminSetting['bobot_ph'],
            'bobot_pts' => (int)$adminSetting['bobot_pts'],
            'bobot_pas' => (int)$adminSetting['bobot_pas']
        ];
    }

    /**
     * Hitung Rekap DKN (Daftar Kumpulan Nilai) per kelas
     */
    public function calculateClassRecap(int $idTp, int $idSmt, int $idKelas): array
    {
        // 1. Ambil daftar mapel yang diajarkan di kelas ini
        $stmtMapel = $this->db->prepare(
            "SELECT DISTINCT m.id_mapel, m.nama_mapel, m.kode AS kode_mapel, m.kelompok, m.urutan_tampil
             FROM master_mapel m
             ORDER BY m.urutan_tampil ASC, m.nama_mapel ASC"
        );
        $stmtMapel->execute();
        $mapelList = $stmtMapel->fetchAll();

        // 2. Ambil seluruh siswa di kelas ini
        $stmtSiswa = $this->db->prepare(
            "SELECT s.id_siswa, s.nama, s.nis, s.nisn, s.jk AS jenis_kelamin
             FROM kelas_siswa ks
             JOIN master_siswa s ON ks.id_siswa = s.id_siswa
             WHERE ks.id_tp = ? AND ks.id_smt = ? AND ks.id_kelas = ?
             ORDER BY s.nama ASC"
        );
        $stmtSiswa->execute([$idTp, $idSmt, $idKelas]);
        $siswaList = $stmtSiswa->fetchAll();

        $studentsData = [];

        foreach ($siswaList as $siswa) {
            $idSiswa = (int)$siswa['id_siswa'];
            $mapelScores = [];
            $totalNilai = 0.0;
            $mapelCount = 0;

            foreach ($mapelList as $mapel) {
                $idMapel = (int)$mapel['id_mapel'];
                $kkmSetting = $this->getKkmValue($idTp, $idSmt, $idKelas, $idMapel);

                $akhirRecord = $this->akhirModel->getSingle($idTp, $idSmt, $idKelas, $idMapel, $idSiswa);
                $ptsRecord = $this->ptsModel->getSingle($idTp, $idSmt, $idKelas, $idMapel, $idSiswa);
                $harianRecord = $this->harianModel->getSingle($idTp, $idSmt, $idKelas, $idMapel, $idSiswa);

                $nilaiAkhir = $akhirRecord && $akhirRecord['akhir'] !== null 
                    ? (float)$akhirRecord['akhir'] 
                    : ($harianRecord && $harianRecord['p_rata_rata'] !== null ? (float)$harianRecord['p_rata_rata'] : 0.0);

                $predikat = $akhirRecord && !empty($akhirRecord['predikat']) 
                    ? $akhirRecord['predikat'] 
                    : $this->calculatePredicate($nilaiAkhir, (float)$kkmSetting['kkm']);

                $mapelScores[$idMapel] = [
                    'kkm' => (float)$kkmSetting['kkm'],
                    'ph' => $harianRecord['p_rata_rata'] ?? null,
                    'pts' => $ptsRecord['nilai'] ?? null,
                    'pas' => $akhirRecord['nilai'] ?? null,
                    'akhir' => $nilaiAkhir > 0 ? $nilaiAkhir : null,
                    'predikat' => $predikat
                ];

                if ($nilaiAkhir > 0) {
                    $totalNilai += $nilaiAkhir;
                    $mapelCount++;
                }
            }

            $rataRata = $mapelCount > 0 ? round($totalNilai / $mapelCount, 2) : 0.0;

            // Catatan kehadiran
            $cwModel = new RaporCatatanWali();
            $cwRecord = $cwModel->getSingle($idTp, $idSmt, $idKelas, $idSiswa);
            $absensi = ['sakit' => 0, 'izin' => 0, 'alfa' => 0];
            if ($cwRecord && !empty($cwRecord['nilai'])) {
                $decoded = json_decode($cwRecord['nilai'], true);
                if (is_array($decoded)) {
                    $absensi = array_merge($absensi, $decoded);
                }
            }

            // Kenaikan / Kelulusan
            $naikModel = new RaporNaik();
            $naikRecord = $naikModel->getSingle($idTp, $idSmt, $idSiswa);
            $statusNaik = $naikRecord ? (int)$naikRecord['naik'] : 1;

            $studentsData[$idSiswa] = [
                'siswa' => $siswa,
                'scores' => $mapelScores,
                'total_nilai' => $totalNilai,
                'rata_rata' => $rataRata,
                'absensi' => $absensi,
                'status_naik' => $statusNaik,
                'ranking' => 0
            ];
        }

        // Hitung Ranking Kelas berdasarkan rata-rata
        uasort($studentsData, function ($a, $b) {
            return $b['rata_rata'] <=> $a['rata_rata'];
        });

        $rank = 1;
        foreach ($studentsData as $idSiswa => &$data) {
            $data['ranking'] = $rank++;
        }
        unset($data);

        // Kembalikan urutan berdasarkan nama siswa untuk kemudahan cetak DKN
        uasort($studentsData, function ($a, $b) {
            return strcmp($a['siswa']['nama'], $b['siswa']['nama']);
        });

        return [
            'mapel_list' => $mapelList,
            'students_data' => $studentsData
        ];
    }
}
