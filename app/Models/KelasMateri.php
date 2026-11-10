<?php

namespace App\Models;

use App\Core\Model;

class KelasMateri extends Model
{
    protected string $table = 'kelas_materi';
    protected string $primaryKey = 'id_materi';

    /**
     * Get list of materials or tasks filtered by TP, SMT, and optional teacher/mapel/kelas/jenis.
     */
    public function getAllByFilter(int $idTp, int $idSmt, ?int $idGuru = null, ?int $jenis = null, ?int $idMapel = null, ?int $idKelas = null): array
    {
        $sql = "SELECT km.*, m.nama_mapel, m.kode as kode_mapel, g.nama_guru, g.kode_guru,
                       (SELECT COUNT(*) FROM kelas_jadwal_materi kjm WHERE kjm.id_materi = km.id_materi) as total_jadwal
                FROM kelas_materi km
                JOIN master_mapel m ON m.id_mapel = km.id_mapel
                JOIN master_guru g ON g.id_guru = km.id_guru
                WHERE km.id_tp = ? AND km.id_smt = ?";

        $params = [$idTp, $idSmt];

        if ($idGuru !== null) {
            $sql .= " AND km.id_guru = ?";
            $params[] = $idGuru;
        }

        if ($jenis !== null) {
            $sql .= " AND km.jenis = ?";
            $params[] = $jenis;
        }

        if ($idMapel !== null) {
            $sql .= " AND km.id_mapel = ?";
            $params[] = $idMapel;
        }

        if ($idKelas !== null) {
            $sql .= " AND JSON_CONTAINS(km.id_kelas, ?)";
            $params[] = json_encode((int)$idKelas);
        }

        $sql .= " ORDER BY km.created_at DESC";

        $rows = $this->query($sql, $params);
        foreach ($rows as &$row) {
            if (is_string($row['id_kelas'])) {
                $row['kelas_ids'] = json_decode($row['id_kelas'], true) ?: [];
            } else {
                $row['kelas_ids'] = (array)($row['id_kelas'] ?? []);
            }
            if (is_string($row['file'])) {
                $row['file_list'] = json_decode($row['file'], true) ?: [];
            } else {
                $row['file_list'] = (array)($row['file'] ?? []);
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * Get detail of a specific material / assignment.
     */
    public function getDetail(int $idMateri): ?array
    {
        $sql = "SELECT km.*, m.nama_mapel, m.kode as kode_mapel, g.nama_guru, g.kode_guru
                FROM kelas_materi km
                JOIN master_mapel m ON m.id_mapel = km.id_mapel
                JOIN master_guru g ON g.id_guru = km.id_guru
                WHERE km.id_materi = ?
                LIMIT 1";

        $rows = $this->query($sql, [$idMateri]);
        if (empty($rows)) {
            return null;
        }

        $row = $rows[0];
        if (is_string($row['id_kelas'])) {
            $row['kelas_ids'] = json_decode($row['id_kelas'], true) ?: [];
        } else {
            $row['kelas_ids'] = (array)($row['id_kelas'] ?? []);
        }
        if (is_string($row['file'])) {
            $row['file_list'] = json_decode($row['file'], true) ?: [];
        } else {
            $row['file_list'] = (array)($row['file'] ?? []);
        }

        return $row;
    }

    /**
     * Strictly verify if a teacher owns or is authorized to modify a material.
     */
    public function isGuruAuthorized(int $idMateri, int $idGuru): bool
    {
        $materi = $this->find($idMateri);
        if (!$materi) {
            return false;
        }
        return (int)$materi['id_guru'] === $idGuru;
    }

    /**
     * Generate unique auto code for materi or tugas.
     */
    public function generateKode(int $jenis, string $mapelKode): string
    {
        $prefix = ($jenis === 1) ? 'MAT' : 'TGS';
        $cleanMapel = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $mapelKode));
        $date = date('ymd');
        $rand = strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 4));
        return "{$prefix}-{$cleanMapel}-{$date}-{$rand}";
    }

    /**
     * Create a new material or assignment.
     */
    public function createMateri(array $data): int|false
    {
        $kelasJson = is_array($data['id_kelas']) ? json_encode(array_values(array_map('intval', $data['id_kelas']))) : $data['id_kelas'];
        $fileJson = !empty($data['file']) ? (is_array($data['file']) ? json_encode($data['file'], JSON_UNESCAPED_UNICODE) : $data['file']) : null;

        $payload = [
            'id_tp' => (int)$data['id_tp'],
            'id_smt' => (int)$data['id_smt'],
            'jenis' => (int)($data['jenis'] ?? 1),
            'kode_materi' => $data['kode_materi'],
            'id_guru' => (int)$data['id_guru'],
            'id_mapel' => (int)$data['id_mapel'],
            'id_kelas' => $kelasJson,
            'judul_materi' => $data['judul_materi'],
            'isi_materi' => $data['isi_materi'],
            'file' => $fileJson,
            'link_video' => !empty($data['link_video']) ? trim($data['link_video']) : null,
            'status' => isset($data['status']) ? (int)$data['status'] : 1,
        ];

        $insertedId = $this->insert($payload);
        return $insertedId ? (int)$insertedId : false;
    }

    /**
     * Update an existing material without destroying previously saved attachments.
     */
    public function updateMateri(int $idMateri, array $data): bool
    {
        $existing = $this->find($idMateri);
        if (!$existing) {
            return false;
        }

        $payload = [];
        if (isset($data['id_mapel'])) {
            $payload['id_mapel'] = (int)$data['id_mapel'];
        }
        if (isset($data['id_kelas'])) {
            $payload['id_kelas'] = is_array($data['id_kelas']) ? json_encode(array_values(array_map('intval', $data['id_kelas']))) : $data['id_kelas'];
        }
        if (isset($data['judul_materi'])) {
            $payload['judul_materi'] = $data['judul_materi'];
        }
        if (isset($data['isi_materi'])) {
            $payload['isi_materi'] = $data['isi_materi'];
        }
        if (array_key_exists('link_video', $data)) {
            $payload['link_video'] = !empty($data['link_video']) ? trim($data['link_video']) : null;
        }
        if (isset($data['status'])) {
            $payload['status'] = (int)$data['status'];
        }

        // Handle file attachments preservation
        if (array_key_exists('file', $data)) {
            $payload['file'] = !empty($data['file']) ? (is_array($data['file']) ? json_encode($data['file'], JSON_UNESCAPED_UNICODE) : $data['file']) : null;
        }

        return $this->update($idMateri, $payload);
    }
}

