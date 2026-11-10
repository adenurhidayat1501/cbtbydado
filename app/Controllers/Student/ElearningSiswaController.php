<?php

namespace App\Controllers\Student;

use App\Core\Controller;
use App\Models\KelasJadwalMateri;
use App\Models\KelasJadwalKbm;
use App\Models\KelasJadwalMapel;
use App\Models\KelasMateriKomentar;
use App\Models\KelasSiswa;
use App\Models\LogMateri;

class ElearningSiswaController extends Controller
{
    private KelasJadwalMateri $kjmModel;
    private KelasJadwalKbm $kbmModel;
    private KelasJadwalMapel $jadwalMapelModel;
    private KelasMateriKomentar $komentarModel;
    private KelasSiswa $kelasSiswaModel;
    private LogMateri $logModel;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->kjmModel = new KelasJadwalMateri();
        $this->kbmModel = new KelasJadwalKbm();
        $this->jadwalMapelModel = new KelasJadwalMapel();
        $this->komentarModel = new KelasMateriKomentar();
        $this->kelasSiswaModel = new KelasSiswa();
        $this->logModel = new LogMateri();
    }

    /**
     * Retrieve the current student record and assigned class for active TP and SMT.
     */
    private function getStudentAndClass(): array
    {
        $siswa = auth_siswa();
        if (!$siswa) {
            set_flash('error', 'Data siswa tidak ditemukan.');
            $this->redirect('/student/dashboard');
            exit;
        }

        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $idSiswa = (int)$siswa['id_siswa'];
        $kelasSiswa = $this->kelasSiswaModel->first([
            'id_siswa' => $idSiswa,
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
        ]);

        $idKelas = (int)($kelasSiswa['id_kelas'] ?? 0);

        return [$siswa, $idKelas, $idTp, $idSmt, $tp, $smt];
    }

    /**
     * Display student learning dashboard with materials and assignments for their class.
     */
    public function index(): void
    {
        [$siswa, $idKelas, $idTp, $idSmt, $tp, $smt] = $this->getStudentAndClass();
        $idSiswa = (int)$siswa['id_siswa'];

        if ($idKelas <= 0) {
            set_flash('error', 'Anda belum terdaftar dalam kelas rombel aktif.');
            $this->redirect('/student/dashboard');
            return;
        }

        $materiList = $this->kjmModel->getStudentSchedule($idTp, $idSmt, $idKelas, $idSiswa, 1);
        $tugasList = $this->kjmModel->getStudentSchedule($idTp, $idSmt, $idKelas, $idSiswa, 2);

        $this->render('student/elearning/index', [
            'title' => 'Ruang Belajar & Tugas — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'siswa' => $siswa,
            'materiList' => $materiList,
            'tugasList' => $tugasList,
        ]);
    }

    /**
     * View material content, embed video, attachments, and atomically log attendance.
     */
    public function bacaMateri(int $idKjm): void
    {
        [$siswa, $idKelas, $idTp, $idSmt, $tp, $smt] = $this->getStudentAndClass();
        $idSiswa = (int)$siswa['id_siswa'];

        $schedule = $this->kjmModel->getDetail($idKjm);
        if (!$schedule || (int)$schedule['id_kelas'] !== $idKelas) {
            set_flash('error', 'Materi tidak ditemukan atau tidak ditujukan untuk kelas Anda.');
            $this->redirect('/student/elearning');
            return;
        }

        // Automatic presence check during KBM hours
        $currentJamKe = $this->detectKbmJamKe($idTp, $idSmt, $idKelas, (int)$schedule['id_mapel']);
        $this->logModel->logRead($idSiswa, $idKjm, $currentJamKe);

        // Fetch comments
        $idMateri = (int)$schedule['id_materi'];
        $comments = $this->komentarModel->getKomentar($idMateri);
        $studentLog = $this->logModel->getStudentLog($idSiswa, $idKjm);

        $this->render('student/elearning/baca_materi', [
            'title' => escape_html($schedule['judul_materi']) . ' — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'siswa' => $siswa,
            'schedule' => $schedule,
            'comments' => $comments,
            'studentLog' => $studentLog,
            'currentJamKe' => $currentJamKe,
        ]);
    }

    /**
     * View assignment instructions and submission form / status.
     */
    public function detailTugas(int $idKjm): void
    {
        [$siswa, $idKelas, $idTp, $idSmt, $tp, $smt] = $this->getStudentAndClass();
        $idSiswa = (int)$siswa['id_siswa'];

        $schedule = $this->kjmModel->getDetail($idKjm);
        if (!$schedule || (int)$schedule['id_kelas'] !== $idKelas) {
            set_flash('error', 'Tugas tidak ditemukan atau tidak ditujukan untuk kelas Anda.');
            $this->redirect('/student/elearning');
            return;
        }

        // Automatic attendance check when opening task
        $currentJamKe = $this->detectKbmJamKe($idTp, $idSmt, $idKelas, (int)$schedule['id_mapel']);
        $this->logModel->logRead($idSiswa, $idKjm, $currentJamKe);

        $studentLog = $this->logModel->getStudentLog($idSiswa, $idKjm);
        $comments = $this->komentarModel->getKomentar((int)$schedule['id_materi']);

        $this->render('student/elearning/tugas_detail', [
            'title' => escape_html($schedule['judul_materi']) . ' — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'siswa' => $siswa,
            'schedule' => $schedule,
            'studentLog' => $studentLog,
            'comments' => $comments,
            'currentJamKe' => $currentJamKe,
        ]);
    }

    /**
     * Submit assignment answer text and/or file attachments.
     */
    public function kumpulTugas(int $idKjm): void
    {
        [$siswa, $idKelas, $idTp, $idSmt, $tp, $smt] = $this->getStudentAndClass();
        $idSiswa = (int)$siswa['id_siswa'];

        $schedule = $this->kjmModel->getDetail($idKjm);
        if (!$schedule || (int)$schedule['id_kelas'] !== $idKelas) {
            set_flash('error', 'Tugas tidak ditemukan.');
            $this->redirect('/student/elearning');
            return;
        }

        $isiTugas = trim((string)$this->request->input('isi_tugas', ''));

        // Handle uploaded file(s)
        $uploadedFiles = [];
        $filesPayload = $_FILES['files'] ?? null;
        if ($filesPayload && !empty($filesPayload['name'][0])) {
            $targetDir = __DIR__ . '/../../../public/uploads/tugas/';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            $count = count($filesPayload['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($filesPayload['error'][$i] === UPLOAD_ERR_OK) {
                    $origName = $filesPayload['name'][$i];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'zip', 'txt'];
                    if (in_array($ext, $allowedExts)) {
                        $safeName = 'ans_' . $idSiswa . '_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
                        if (move_uploaded_file($filesPayload['tmp_name'][$i], $targetDir . $safeName)) {
                            $uploadedFiles[] = [
                                'orig_name' => $origName,
                                'filename' => $safeName,
                                'path' => 'uploads/tugas/' . $safeName,
                                'size' => $filesPayload['size'][$i],
                            ];
                        }
                    }
                }
            }
        }

        if (empty($isiTugas) && empty($uploadedFiles)) {
            set_flash('error', 'Jawaban teks atau lampiran berkas tugas wajib diisi.');
            $this->redirect('/student/elearning/tugas/' . $idKjm);
            return;
        }

        $currentJamKe = $this->detectKbmJamKe($idTp, $idSmt, $idKelas, (int)$schedule['id_mapel']);
        $submitted = $this->logModel->submitTugas($idSiswa, $idKjm, $isiTugas, $uploadedFiles, $currentJamKe);

        if ($submitted) {
            set_flash('success', 'Tugas berhasil dikumpulkan!');
        } else {
            set_flash('error', 'Gagal mengumpulkan tugas. Silakan coba lagi.');
        }

        $this->redirect('/student/elearning/tugas/' . $idKjm);
    }

    /**
     * Post a comment from student in Ruang Belajar.
     */
    public function postKomentar(int $idMateri): void
    {
        $komentar = trim((string)$this->request->input('komentar', ''));
        $idKjm = (int)$this->request->input('id_kjm', 0);
        $userId = auth_id();

        if (empty($komentar) || !$userId) {
            set_flash('error', 'Komentar tidak boleh kosong.');
            $redirectUrl = $idKjm > 0 ? '/student/elearning/baca/' . $idKjm : '/student/elearning';
            $this->redirect($redirectUrl);
            return;
        }

        $this->komentarModel->addKomentar($idMateri, $userId, $komentar);
        set_flash('success', 'Komentar berhasil dikirim.');

        $redirectUrl = $idKjm > 0 ? '/student/elearning/baca/' . $idKjm : '/student/elearning';
        $this->redirect($redirectUrl);
    }

    /**
     * Check if currently within scheduled KBM hours for this subject and class.
     * Returns the matching jam_ke (e.g. 1, 2, ..) or null if outside hours.
     */
    private function detectKbmJamKe(int $idTp, int $idSmt, int $idKelas, int $idMapel): ?int
    {
        // 1. Get today's day number: 1=Senin, 2=Selasa, ..., 6=Sabtu, 7=Minggu
        $dayOfWeek = (int)date('N'); // 1 (for Monday) through 7 (for Sunday)

        // 2. Check if this subject is on today's schedule for this class
        $jadwalMapel = $this->jadwalMapelModel->findJadwalMapel($idTp, $idSmt, $idKelas, $idMapel, $dayOfWeek);
        if (!$jadwalMapel) {
            return null;
        }

        // 3. Get class KBM time slot settings
        $kbmSetting = $this->kbmModel->getSetting($idTp, $idSmt, $idKelas);
        $activeJamKe = $this->kbmModel->getCurrentJamKe($kbmSetting);

        // If the active period matches the scheduled period for this subject
        if ($activeJamKe !== null && (int)$jadwalMapel['jam_ke'] === $activeJamKe) {
            return $activeJamKe;
        }

        // Even if slightly outside exact minute, if it's the scheduled day, return the period
        return (int)$jadwalMapel['jam_ke'];
    }
}
