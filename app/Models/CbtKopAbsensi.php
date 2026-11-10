<?php

namespace App\Models;

use App\Core\Model;

class CbtKopAbsensi extends Model
{
    protected string $table = 'cbt_kop_absensi';
    protected string $primaryKey = 'id_kop';

    /**
     * Get active header configuration for attendance sheets & exam reports.
     */
    public function getActive(): array
    {
        $row = $this->first([]);
        if ($row) {
            return $row;
        }

        return [
            'id_kop' => 1,
            'header_1' => 'PEMERINTAH DAERAH PROVINSI',
            'header_2' => 'DINAS PENDIDIKAN',
            'header_3' => 'DADO CBT',
            'header_4' => 'ASESMEN SUMATIF BERBASIS KOMPUTER (CBT AKM)',
            'proktor' => 'Proktor CBT',
            'pengawas_1' => 'Pengawas Ruang 1',
            'pengawas_2' => 'Pengawas Ruang 2',
            'tanggal' => date('d F Y'),
            'kota' => 'Kota Bandung',
        ];
    }

    /**
     * Update or save exam administration header.
     */
    public function saveKop(array $data): bool
    {
        $existing = $this->first([]);
        $payload = [
            'header_1' => $data['header_1'] ?? null,
            'header_2' => $data['header_2'] ?? null,
            'header_3' => $data['header_3'] ?? null,
            'header_4' => $data['header_4'] ?? null,
            'proktor' => $data['proktor'] ?? null,
            'pengawas_1' => $data['pengawas_1'] ?? null,
            'pengawas_2' => $data['pengawas_2'] ?? null,
            'tanggal' => $data['tanggal'] ?? null,
            'kota' => $data['kota'] ?? null,
        ];

        if ($existing) {
            return $this->update($existing['id_kop'], $payload);
        } else {
            return (bool)$this->insert($payload);
        }
    }
}
