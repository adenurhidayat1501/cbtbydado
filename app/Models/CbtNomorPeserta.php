<?php

namespace App\Models;

use App\Core\Model;

class CbtNomorPeserta extends Model
{
    protected string $table = 'cbt_nomor_peserta';
    protected string $primaryKey = 'id_nomor';

    /**
     * Get students with their exam numbers for a period and optional class.
     */
    public function getStudentsWithNomor(int $idTp, int $idSmt, ?int $idKelas = null): array
    {
        $sql = "SELECT s.id_siswa, s.nis, s.nisn, s.nama, s.jk,
                       k.nama_kelas, k.level_id,
                       np.id_nomor, np.nomor_peserta
                FROM kelas_siswa ks
                JOIN master_siswa s ON s.id_siswa = ks.id_siswa
                JOIN master_kelas k ON k.id_kelas = ks.id_kelas
                LEFT JOIN cbt_nomor_peserta np ON np.id_siswa = s.id_siswa AND np.id_tp = ?
                WHERE ks.id_tp = ? AND ks.id_smt = ?";

        $params = [$idTp, $idTp, $idSmt];

        if ($idKelas !== null && $idKelas > 0) {
            $sql .= " AND ks.id_kelas = ?";
            $params[] = $idKelas;
        }

        $sql .= " ORDER BY k.level_id ASC, k.nama_kelas ASC, s.nama ASC";

        return $this->query($sql, $params);
    }

    /**
     * Auto generate exam participant numbers for students in a class or all classes.
     * Default format: [YY]-[JJ]-[SSS]-[NNNN] e.g. "26-02-001-0001"
     */
    public function generateNomor(int $idTp, int $idSmt, ?int $idKelas = null, string $prefix = ''): int
    {
        // Get active school setting for jenjang
        $settingSekolah = (new SettingSekolah())->getActive();
        $jenjang = (int)($settingSekolah['jenjang'] ?? 2);
        $jenjangCode = sprintf('%02d', $jenjang);

        // Get year code (e.g. "2025/2026" -> "26")
        $masterTp = (new MasterTp())->find($idTp);
        $tahunStr = $masterTp['tahun'] ?? date('Y');
        $tahunCode = date('y');
        if (preg_match('/(\d{4})[^\d]*(\d{4})?/', $tahunStr, $m)) {
            $tahunCode = substr($m[2] ?? $m[1], -2);
        }

        if (empty($prefix)) {
            $schoolCode = sprintf('%03d', (int)substr($settingSekolah['npsn'] ?? '1', -3));
            $prefix = "{$tahunCode}-{$jenjangCode}-{$schoolCode}-";
        }

        $students = $this->getStudentsWithNomor($idTp, $idSmt, $idKelas);
        $generatedCount = 0;
        $counter = 1;

        foreach ($students as $stu) {
            $idSiswa = (int)$stu['id_siswa'];
            $nomorPeserta = $prefix . sprintf('%04d', $counter++);

            $existing = $this->first([
                'id_siswa' => $idSiswa,
                'id_tp' => $idTp,
            ]);

            if ($existing) {
                $this->update($existing['id_nomor'], ['nomor_peserta' => $nomorPeserta]);
            } else {
                $this->insert([
                    'id_siswa' => $idSiswa,
                    'id_tp' => $idTp,
                    'nomor_peserta' => $nomorPeserta,
                ]);
            }
            $generatedCount++;
        }

        return $generatedCount;
    }

    /**
     * Save or update exam participant number for a single student.
     */
    public function saveNomor(int $idTp, int $idSiswa, string $nomor): bool
    {
        $nomor = trim($nomor);
        if (empty($nomor)) {
            return false;
        }

        $existing = $this->first([
            'id_siswa' => $idSiswa,
            'id_tp' => $idTp,
        ]);

        if ($existing) {
            return $this->update($existing['id_nomor'], ['nomor_peserta' => $nomor]);
        } else {
            return (bool)$this->insert([
                'id_siswa' => $idSiswa,
                'id_tp' => $idTp,
                'nomor_peserta' => $nomor,
            ]);
        }
    }
}
