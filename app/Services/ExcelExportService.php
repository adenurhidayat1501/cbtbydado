<?php

namespace App\Services;

class ExcelExportService
{
    /**
     * Generate dynamic CSV / Excel output with UTF-8 BOM for CBT Exam results.
     * Prevents shifted headers when exam package does not contain multiple choice questions.
     */
    public function exportExamRecap(array $jadwal, array $rekapData): void
    {
        $filename = 'Rekap_Nilai_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $jadwal['bank_kode'] ?? 'Ujian') . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // Write UTF-8 BOM for Microsoft Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Build dynamic headers based on active question types
        $headers = ['No', 'Nomor Peserta', 'NIS', 'Nama Siswa', 'Kelas', 'Ruang', 'Sesi'];

        $hasPg = ((int)($jadwal['tampil_pg'] ?? 0)) > 0;
        $hasKompleks = ((int)($jadwal['tampil_kompleks'] ?? 0)) > 0;
        $hasJodohkan = ((int)($jadwal['tampil_jodohkan'] ?? 0)) > 0;
        $hasIsian = ((int)($jadwal['tampil_isian'] ?? 0)) > 0;
        $hasEsai = ((int)($jadwal['tampil_esai'] ?? 0)) > 0;

        if ($hasPg) {
            $headers[] = 'PG Benar';
            $headers[] = 'Nilai PG';
        }
        if ($hasKompleks) {
            $headers[] = 'Nilai PG Kompleks';
        }
        if ($hasJodohkan) {
            $headers[] = 'Nilai Menjodohkan';
        }
        if ($hasIsian) {
            $headers[] = 'Nilai Isian';
        }
        if ($hasEsai) {
            $headers[] = 'Nilai Esai';
        }

        $headers[] = 'Total Nilai Asli';
        $headers[] = 'Nilai Konversi';

        fputcsv($output, $headers);

        $no = 1;
        foreach ($rekapData as $row) {
            $line = [
                $no++,
                $row['nomor_peserta'] ?? '-',
                $row['nis'] ?? '-',
                $row['nama'] ?? '-',
                $row['nama_kelas'] ?? '-',
                $row['nama_ruang'] ?? '-',
                $row['nama_sesi'] ?? '-',
            ];

            if ($hasPg) {
                $line[] = $row['pg_benar'] ?? 0;
                $line[] = $row['pg_nilai'] ?? 0.00;
            }
            if ($hasKompleks) {
                $line[] = $row['kompleks_nilai'] ?? 0.00;
            }
            if ($hasJodohkan) {
                $line[] = $row['jodohkan_nilai'] ?? 0.00;
            }
            if ($hasIsian) {
                $line[] = $row['isian_nilai'] ?? 0.00;
            }
            if ($hasEsai) {
                $line[] = $row['essai_nilai'] ?? 0.00;
            }

            $line[] = $row['total_nilai'] ?? 0.00;
            $line[] = $row['nilai_konversi'] ?? $row['total_nilai'] ?? 0.00;

            fputcsv($output, $line);
        }

        fclose($output);
        exit;
    }

    /**
     * Export E-Learning assignment grades recap to CSV / Excel.
     */
    public function exportElearningRecap(array $meta, array $tasks, array $studentsRecap): void
    {
        $kelasName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $meta['kelas']['nama_kelas'] ?? 'SemuaKelas');
        $mapelName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $meta['mapel']['kode'] ?? 'SemuaMapel');
        $filename = "Rekap_Nilai_Elearning_{$kelasName}_{$mapelName}_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        // Header info
        fputcsv($output, ['REKAP NILAI HARIAN E-LEARNING']);
        fputcsv($output, ['Kelas', $meta['kelas']['nama_kelas'] ?? '-']);
        fputcsv($output, ['Mata Pelajaran', $meta['mapel']['nama_mapel'] ?? 'Semua Mapel']);
        fputcsv($output, ['Tahun Pelajaran / Semester', ($meta['tp']['tahun'] ?? '-') . ' - Semester ' . ($meta['smt']['nama_smt'] ?? '-')]);
        fputcsv($output, ['Tanggal Unduh', date('d-m-Y H:i:s')]);
        fputcsv($output, []);

        // Table headers
        $headers = ['No', 'NIS', 'Nama Siswa'];
        foreach ($tasks as $t) {
            $tgl = date('d/m', strtotime($t['jadwal_materi']));
            $headers[] = ($t['kode_materi'] ?? 'TGS') . " ({$tgl})";
        }
        $headers[] = 'Tugas Terkoreksi';
        $headers[] = 'Rata-rata Nilai';

        fputcsv($output, $headers);

        $no = 1;
        foreach ($studentsRecap as $row) {
            $stu = $row['siswa'];
            $line = [
                $no++,
                $stu['nis'] ?? '-',
                $stu['nama'] ?? '-',
            ];

            foreach ($tasks as $t) {
                $idKjm = $t['id_kjm'];
                $scoreItem = $row['scores'][$idKjm] ?? null;
                if ($scoreItem && $scoreItem['nilai'] !== null) {
                    $line[] = number_format((float)$scoreItem['nilai'], 2, '.', '');
                } elseif ($scoreItem && $scoreItem['submitted']) {
                    $line[] = 'Belum Dinilai';
                } else {
                    $line[] = '-';
                }
            }

            $line[] = $row['count_scored'] ?? 0;
            $line[] = number_format((float)($row['rata_rata'] ?? 0), 2, '.', '');

            fputcsv($output, $line);
        }

        fclose($output);
        exit;
    }

    /**
     * Export daily KBM attendance recap to CSV.
     */
    public function exportKehadiranHarian(array $meta, array $attendanceData): void
    {
        $kelasName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $meta['kelas']['nama_kelas'] ?? 'Kelas');
        $mapelName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $meta['mapel']['kode'] ?? 'Mapel');
        $dateStr = preg_replace('/[^0-9]/', '', $meta['date'] ?? date('Ymd'));
        $filename = "Presensi_Harian_{$kelasName}_{$mapelName}_{$dateStr}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, ['REKAP PRESENSI HARIAN KBM']);
        fputcsv($output, ['Kelas', $meta['kelas']['nama_kelas'] ?? '-']);
        fputcsv($output, ['Mata Pelajaran', $meta['mapel']['nama_mapel'] ?? '-']);
        fputcsv($output, ['Tanggal', $meta['date'] ?? date('Y-m-d')]);
        fputcsv($output, []);

        fputcsv($output, ['No', 'NIS', 'Nama Siswa', 'L/P', 'Status Kehadiran', 'Waktu Akses', 'Jam Ke']);

        $no = 1;
        foreach ($attendanceData as $row) {
            $isHadir = !empty($row['id_log']);
            $statusStr = $isHadir ? 'Hadir' : 'Tidak Hadir';
            $waktu = $isHadir ? ($row['log_time'] ?? '-') : '-';
            $jamKe = $isHadir ? ($row['jam_ke'] ?? '-') : '-';

            fputcsv($output, [
                $no++,
                $row['nis'] ?? '-',
                $row['nama'] ?? '-',
                $row['jk'] ?? '-',
                $statusStr,
                $waktu,
                $jamKe,
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export monthly KBM attendance recap to CSV.
     */
    public function exportKehadiranBulanan(array $meta, array $meetings, array $studentsRecap): void
    {
        $kelasName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $meta['kelas']['nama_kelas'] ?? 'Kelas');
        $mapelName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $meta['mapel']['kode'] ?? 'Mapel');
        $filename = "Presensi_Bulanan_{$kelasName}_{$mapelName}_{$meta['tahun']}_{$meta['bulan']}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, ['REKAP PRESENSI BULANAN KBM']);
        fputcsv($output, ['Kelas', $meta['kelas']['nama_kelas'] ?? '-']);
        fputcsv($output, ['Mata Pelajaran', $meta['mapel']['nama_mapel'] ?? '-']);
        fputcsv($output, ['Bulan / Tahun', ($meta['nama_bulan'] ?? $meta['bulan']) . ' ' . $meta['tahun']]);
        fputcsv($output, []);

        $headers = ['No', 'NIS', 'Nama Siswa', 'L/P'];
        foreach ($meetings as $m) {
            $headers[] = date('d/m', strtotime($m['jadwal_materi']));
        }
        $headers[] = 'Total Hadir';
        $headers[] = 'Total Pertemuan';
        $headers[] = 'Persentase (%)';

        fputcsv($output, $headers);

        $no = 1;
        foreach ($studentsRecap as $row) {
            $stu = $row['siswa'];
            $line = [
                $no++,
                $stu['nis'] ?? '-',
                $stu['nama'] ?? '-',
                $stu['jk'] ?? '-',
            ];

            foreach ($meetings as $m) {
                $status = $row['meeting_status'][$m['id_kjm']] ?? ['hadir' => false];
                $line[] = $status['hadir'] ? 'H' : '-';
            }

            $line[] = $row['total_hadir'] ?? 0;
            $line[] = $row['total_pertemuan'] ?? count($meetings);
            $line[] = ($row['persentase'] ?? 0) . '%';

            fputcsv($output, $line);
        }

        fclose($output);
        exit;
    }
}
