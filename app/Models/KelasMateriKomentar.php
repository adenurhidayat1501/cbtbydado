<?php

namespace App\Models;

use App\Core\Model;

class KelasMateriKomentar extends Model
{
    protected string $table = 'kelas_materi_komentar';
    protected string $primaryKey = 'id_komentar';

    /**
     * Retrieve comments for a material with user profile and role details.
     */
    public function getKomentar(int $idMateri): array
    {
        $sql = "SELECT kmk.*, u.username, u.role,
                       COALESCE(g.nama_guru, s.nama, u.username) as author_name,
                       COALESCE(g.foto, s.foto) as author_foto
                FROM kelas_materi_komentar kmk
                JOIN users u ON u.id = kmk.id_user
                LEFT JOIN master_guru g ON g.id_user = u.id
                LEFT JOIN master_siswa s ON s.id_user = u.id
                WHERE kmk.id_materi = ?
                ORDER BY kmk.created_at ASC";

        return $this->query($sql, [$idMateri]);
    }

    /**
     * Post a new comment to a material.
     */
    public function addKomentar(int $idMateri, int $idUser, string $komentar): bool
    {
        $komentar = trim($komentar);
        if (empty($komentar)) {
            return false;
        }

        return (bool)$this->insert([
            'id_materi' => $idMateri,
            'id_user' => $idUser,
            'komentar' => $komentar,
        ]);
    }
}

