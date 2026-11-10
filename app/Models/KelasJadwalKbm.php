<?php

namespace App\Models;

use App\Core\Model;

class KelasJadwalKbm extends Model
{
    protected string $table = 'kelas_jadwal_kbm';
    protected string $primaryKey = 'id_kbm';

    /**
     * Get KBM schedule settings for a specific class in an academic period.
     * Returns default values if no custom record has been saved yet.
     */
    public function getSetting(int $idTp, int $idSmt, int $idKelas): array
    {
        $setting = $this->first([
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_kelas' => $idKelas,
        ]);

        if ($setting) {
            if (is_string($setting['istirahat'])) {
                $setting['istirahat'] = json_decode($setting['istirahat'], true) ?: [];
            }
            return $setting;
        }

        return [
            'id_kbm' => null,
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_kelas' => $idKelas,
            'kbm_jam_pel' => 40,
            'kbm_jam_mulai' => '07:00:00',
            'kbm_jml_mapel_hari' => 6,
            'istirahat' => [
                ['ist' => 1, 'mulai' => '09:40', 'durasi' => 20],
                ['ist' => 2, 'mulai' => '11:40', 'durasi' => 40],
            ],
        ];
    }

    /**
     * Save or update KBM settings for a specific class.
     */
    public function saveSetting(int $idTp, int $idSmt, int $idKelas, array $data): bool
    {
        $existing = $this->first([
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_kelas' => $idKelas,
        ]);

        $istirahatJson = null;
        if (!empty($data['istirahat'])) {
            $istirahatJson = is_array($data['istirahat']) ? json_encode($data['istirahat'], JSON_UNESCAPED_UNICODE) : $data['istirahat'];
        }

        $jamMulai = $data['kbm_jam_mulai'] ?? '07:00:00';
        if (strlen($jamMulai) === 5) {
            $jamMulai .= ':00';
        }

        $payload = [
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'id_kelas' => $idKelas,
            'kbm_jam_pel' => max(15, (int)($data['kbm_jam_pel'] ?? 40)),
            'kbm_jam_mulai' => $jamMulai,
            'kbm_jml_mapel_hari' => max(1, min(12, (int)($data['kbm_jml_mapel_hari'] ?? 6))),
            'istirahat' => $istirahatJson,
        ];

        if ($existing) {
            return $this->update($existing['id_kbm'], $payload);
        } else {
            return (bool)$this->insert($payload);
        }
    }

    /**
     * Calculate start and end time for each period (jam ke-1 .. N) and break intervals.
     */
    public function getTimeSlots(array $setting): array
    {
        $jamMulaiStr = substr($setting['kbm_jam_mulai'] ?? '07:00:00', 0, 5);
        $jamPelMenit = (int)($setting['kbm_jam_pel'] ?? 40);
        $jmlJam = (int)($setting['kbm_jml_mapel_hari'] ?? 6);
        $istirahatList = $setting['istirahat'] ?? [];

        if (is_string($istirahatList)) {
            $istirahatList = json_decode($istirahatList, true) ?: [];
        }

        $slots = [];
        $currentTimestamp = strtotime('2000-01-01 ' . $jamMulaiStr . ':00');

        for ($i = 1; $i <= $jmlJam; $i++) {
            $currentTimeStr = date('H:i', $currentTimestamp);

            // Check if there is a break scheduled at this exact time
            foreach ($istirahatList as $ist) {
                $istMulai = substr($ist['mulai'] ?? '', 0, 5);
                $istDurasi = (int)($ist['durasi'] ?? 15);
                if ($currentTimeStr === $istMulai && $istDurasi > 0) {
                    $endBreakTimestamp = $currentTimestamp + ($istDurasi * 60);
                    $slots[] = [
                        'type' => 'istirahat',
                        'jam_ke' => null,
                        'label' => 'Istirahat (' . ($ist['ist'] ?? '') . ')',
                        'mulai' => $currentTimeStr,
                        'selesai' => date('H:i', $endBreakTimestamp),
                        'durasi' => $istDurasi,
                        'is_istirahat' => true,
                    ];
                    $currentTimestamp = $endBreakTimestamp;
                    $currentTimeStr = date('H:i', $currentTimestamp);
                }
            }

            $endPeriodTimestamp = $currentTimestamp + ($jamPelMenit * 60);
            $slots[] = [
                'type' => 'pelajaran',
                'jam_ke' => $i,
                'label' => 'Jam Ke-' . $i,
                'mulai' => $currentTimeStr,
                'selesai' => date('H:i', $endPeriodTimestamp),
                'durasi' => $jamPelMenit,
                'is_istirahat' => false,
            ];

            $currentTimestamp = $endPeriodTimestamp;
        }

        return $slots;
    }

    /**
     * Determine which jam_ke is currently active at a given time string (H:i).
     */
    public function getCurrentJamKe(array $setting, ?string $timeStr = null): ?int
    {
        if ($timeStr === null) {
            $timeStr = date('H:i');
        } else {
            $timeStr = substr($timeStr, 0, 5);
        }

        $slots = $this->getTimeSlots($setting);
        $targetTime = strtotime('2000-01-01 ' . $timeStr . ':00');

        foreach ($slots as $slot) {
            if ($slot['is_istirahat']) {
                continue;
            }
            $start = strtotime('2000-01-01 ' . $slot['mulai'] . ':00');
            $end = strtotime('2000-01-01 ' . $slot['selesai'] . ':00');

            if ($targetTime >= $start && $targetTime < $end) {
                return (int)$slot['jam_ke'];
            }
        }

        return null;
    }
}

