<?php

namespace App\Models;

use App\Core\Model;

class RaporAdminSetting extends Model
{
    protected string $table = 'rapor_admin_setting';
    protected string $primaryKey = 'id_setting';

    public function getSetting(int $idTp, int $idSmt): array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE id_tp = ? AND id_smt = ? LIMIT 1");
        $stmt->execute([$idTp, $idSmt]);
        $row = $stmt->fetch();

        if ($row) {
            return $row;
        }

        // Return default configuration if not configured yet
        return [
            'id_setting' => 0,
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'kkm_tunggal' => 1,
            'kkm' => 75,
            'bobot_ph' => 50,
            'bobot_pts' => 25,
            'bobot_pas' => 25,
            'bobot_absen' => 0,
            'tgl_rapor_akhir' => date('Y-m-d'),
            'tgl_rapor_pts' => date('Y-m-d'),
            'tgl_rapor_kelas_akhir' => date('Y-m-d'),
            'nip_kepsek' => 1,
            'nip_walikelas' => 1
        ];
    }

    public function saveSetting(int $idTp, int $idSmt, array $data): bool
    {
        $existing = $this->getSetting($idTp, $idSmt);
        $payload = [
            'kkm_tunggal' => isset($data['kkm_tunggal']) ? (int)$data['kkm_tunggal'] : 1,
            'kkm' => isset($data['kkm']) ? (int)$data['kkm'] : 75,
            'bobot_ph' => isset($data['bobot_ph']) ? (int)$data['bobot_ph'] : 50,
            'bobot_pts' => isset($data['bobot_pts']) ? (int)$data['bobot_pts'] : 25,
            'bobot_pas' => isset($data['bobot_pas']) ? (int)$data['bobot_pas'] : 25,
            'bobot_absen' => isset($data['bobot_absen']) ? (int)$data['bobot_absen'] : 0,
            'tgl_rapor_akhir' => !empty($data['tgl_rapor_akhir']) ? $data['tgl_rapor_akhir'] : null,
            'tgl_rapor_pts' => !empty($data['tgl_rapor_pts']) ? $data['tgl_rapor_pts'] : null,
            'tgl_rapor_kelas_akhir' => !empty($data['tgl_rapor_kelas_akhir']) ? $data['tgl_rapor_kelas_akhir'] : null,
            'nip_kepsek' => isset($data['nip_kepsek']) ? (int)$data['nip_kepsek'] : 1,
            'nip_walikelas' => isset($data['nip_walikelas']) ? (int)$data['nip_walikelas'] : 1
        ];

        if ($existing['id_setting'] > 0) {
            return $this->update((int)$existing['id_setting'], $payload);
        } else {
            $payload['id_tp'] = $idTp;
            $payload['id_smt'] = $idSmt;
            return (bool)$this->insert($payload);
        }
    }
}

