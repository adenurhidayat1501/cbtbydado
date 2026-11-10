<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Core\Controller;
use App\Models\CbtBankSoal;
use App\Models\CbtDurasiSiswa;
use App\Models\CbtJadwal;
use App\Models\CbtNilai;
use App\Models\CbtSesiSiswa;
use App\Models\CbtSoalSiswa;
use App\Models\CbtToken;
use App\Models\SettingSekolah;
use App\Services\AutoGraderService;
use App\Services\QuestionRandomizerService;

class ExamEngineController extends Controller
{
    private CbtJadwal $jadwalModel;
    private CbtBankSoal $bankModel;
    private CbtDurasiSiswa $durasiModel;
    private CbtSoalSiswa $soalSiswaModel;
    private CbtNilai $nilaiModel;
    private CbtToken $tokenModel;
    private CbtSesiSiswa $sesiSiswaModel;
    private QuestionRandomizerService $randomizer;
    private AutoGraderService $grader;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->jadwalModel = new CbtJadwal();
        $this->bankModel = new CbtBankSoal();
        $this->durasiModel = new CbtDurasiSiswa();
        $this->soalSiswaModel = new CbtSoalSiswa();
        $this->nilaiModel = new CbtNilai();
        $this->tokenModel = new CbtToken();
        $this->sesiSiswaModel = new CbtSesiSiswa();
        $this->randomizer = new QuestionRandomizerService();
        $this->grader = new AutoGraderService();
    }

    /**
     * List of available/active CBT exams for student.
     */
    public function index(): void
    {
        $siswa = auth_siswa();
        if (!$siswa) {
            $this->redirect('/login');
            return;
        }

        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);
        $idSiswa = (int)$siswa['id_siswa'];

        // Get student's class
        $kelasSiswa = (new \App\Models\KelasSiswa())->first([
            'id_siswa' => $idSiswa,
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
        ]);
        $idKelas = (int)($kelasSiswa['id_kelas'] ?? 0);

        // Fetch active schedules for this period
        $activeSchedules = $this->jadwalModel->getAllByFilter($idTp, $idSmt);
        $filteredSchedules = [];

        foreach ($activeSchedules as $sch) {
            if ((int)$sch['status'] !== 1) {
                continue;
            }

            // Check if class is assigned to this bank soal
            $bankKelas = is_string($sch['bank_kelas']) ? json_decode($sch['bank_kelas'], true) : $sch['bank_kelas'];
            if (!empty($bankKelas) && is_array($bankKelas) && !in_array($idKelas, $bankKelas)) {
                continue;
            }

            // Check duration status
            $session = $this->durasiModel->getSession((int)$sch['id_jadwal'], $idSiswa);
            $sch['session'] = $session;
            $sch['status_pengerjaan'] = $session ? (int)$session['status'] : 0; // 0=Belum, 1=Sedang, 2=Selesai

            // Check if score is available
            $sch['nilai'] = $this->nilaiModel->getScore((int)$sch['id_jadwal'], $idSiswa);

            $filteredSchedules[] = $sch;
        }

        $this->render('student/cbt/index', [
            'title' => 'Portal Ujian CBT Siswa — DADO CBT',
            'siswa' => $siswa,
            'tp' => $tp,
            'smt' => $smt,
            'schedules' => $filteredSchedules,
        ], 'layouts/master');
    }

    /**
     * Token confirmation view before starting the exam.
     */
    public function konfirmasi(int $idJadwal): void
    {
        $siswa = auth_siswa();
        if (!$siswa) {
            $this->redirect('/login');
            return;
        }

        $idSiswa = (int)$siswa['id_siswa'];
        $jadwal = $this->jadwalModel->getDetail($idJadwal);
        if (!$jadwal) {
            set_flash('error', 'Jadwal ujian tidak ditemukan.');
            $this->redirect('/student/cbt');
            return;
        }

        $session = $this->durasiModel->getSession($idJadwal, $idSiswa);
        if ($session && (int)$session['status'] === 2 && (int)($session['reset'] ?? 0) !== 1) {
            // Exam already finished and no proctor reset
            set_flash('info', 'Anda telah menyelesaikan ujian ini.');
            $this->redirect('/student/cbt/selesai/' . $idJadwal);
            return;
        }

        $this->render('student/cbt/konfirmasi', [
            'title' => 'Konfirmasi Tes: ' . ($jadwal['bank_kode'] ?? 'Ujian') . ' — DADO CBT',
            'siswa' => $siswa,
            'jadwal' => $jadwal,
            'session' => $session,
        ], 'layouts/master');
    }

    /**
     * Start or resume exam. Validates token, initializes questions and timer.
     */
    public function start(int $idJadwal): void
    {
        $siswa = auth_siswa();
        if (!$siswa) {
            $this->redirect('/login');
            return;
        }

        $idSiswa = (int)$siswa['id_siswa'];
        $jadwal = $this->jadwalModel->getDetail($idJadwal);
        if (!$jadwal) {
            set_flash('error', 'Jadwal ujian tidak ditemukan.');
            $this->redirect('/student/cbt');
            return;
        }

        // Validate Token if required
        if ((int)($jadwal['token'] ?? 1) === 1) {
            $inputToken = strtoupper(trim((string)$this->request->input('token', '')));
            $activeTokenData = $this->tokenModel->getActiveToken();
            $serverToken = strtoupper(trim((string)($activeTokenData['token'] ?? '')));

            if (empty($inputToken) || $inputToken !== $serverToken) {
                set_flash('error', 'Token ujian yang Anda masukkan tidak valid atau telah kadaluarsa.');
                $this->redirect('/student/cbt/konfirmasi/' . $idJadwal);
                return;
            }
        }

        $durasiMenit = (int)$jadwal['durasi_ujian'];

        // 1. Start or resume session in cbt_durasi_siswa
        $this->durasiModel->startExam($idJadwal, $idSiswa, $durasiMenit);

        // 2. Initialize or fetch randomized questions
        $this->randomizer->initializeExamQuestions($idJadwal, $idSiswa);

        // 3. Redirect to interactive exam room
        $this->redirect('/student/cbt/ujian/' . $idJadwal);
    }

    /**
     * Interactive CBT Exam Room.
     */
    public function ujian(int $idJadwal): void
    {
        $siswa = auth_siswa();
        if (!$siswa) {
            $this->redirect('/login');
            return;
        }

        $idSiswa = (int)$siswa['id_siswa'];
        $jadwal = $this->jadwalModel->getDetail($idJadwal);
        if (!$jadwal) {
            $this->redirect('/student/cbt');
            return;
        }

        $session = $this->durasiModel->getSession($idJadwal, $idSiswa);
        if (!$session || (int)$session['status'] === 0) {
            $this->redirect('/student/cbt/konfirmasi/' . $idJadwal);
            return;
        }

        // Check if exam already finished
        if ((int)$session['status'] === 2 && (int)($session['reset'] ?? 0) !== 1) {
            $this->redirect('/student/cbt/selesai/' . $idJadwal);
            return;
        }

        // Calculate remaining seconds based on server time
        $durasiSeconds = (int)$jadwal['durasi_ujian'] * 60;
        $startTime = strtotime($session['mulai'] ?? date('Y-m-d H:i:s'));
        $elapsedSeconds = max(0, time() - $startTime);
        $remainingSeconds = max(0, $durasiSeconds - $elapsedSeconds);

        // If time is up, auto finish
        if ($remainingSeconds <= 0 && $elapsedSeconds > 0) {
            $this->durasiModel->finishExam($idJadwal, $idSiswa);
            $this->grader->gradeExam($idJadwal, $idSiswa);
            $this->redirect('/student/cbt/selesai/' . $idJadwal);
            return;
        }

        // Retrieve randomized questions for this student
        $questions = $this->soalSiswaModel->getStudentQuestions($idJadwal, $idSiswa);

        // Fetch school settings for brand
        $settingSekolah = (new SettingSekolah())->getActive();

        // Render full exam layout (without standard dashboard sidebar)
        $this->render('student/cbt/ujian', [
            'title' => 'Ujian: ' . ($jadwal['bank_kode'] ?? 'CBT') . ' — ' . ($settingSekolah['nama_sekolah'] ?? 'DADO CBT'),
            'siswa' => $siswa,
            'jadwal' => $jadwal,
            'session' => $session,
            'questions' => $questions,
            'remainingSeconds' => $remainingSeconds,
            'settingSekolah' => $settingSekolah,
        ], false);
    }

    /**
     * AJAX endpoint for atomic single-row answer save.
     */
    public function saveAnswer(): void
    {
        $siswa = auth_siswa();
        if (!$siswa) {
            $this->response->json(['status' => 'error', 'message' => 'Sesi login telah berakhir.'], 401);
            return;
        }

        $idSoalSiswa = (int)$this->request->input('id_soal_siswa', 0);
        $jawaban = $this->request->input('jawaban', null);
        $jawabanAlias = $this->request->input('jawaban_alias', null);
        $ragu = (int)$this->request->input('ragu', 0);

        if ($idSoalSiswa <= 0) {
            $this->response->json(['status' => 'error', 'message' => 'Parameter tidak valid.'], 400);
            return;
        }

        // Verify that this question belongs to the logged-in student
        $soalRow = $this->soalSiswaModel->find($idSoalSiswa);
        if (!$soalRow || (int)$soalRow['id_siswa'] !== (int)$siswa['id_siswa']) {
            $this->response->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
            return;
        }

        $saved = $this->soalSiswaModel->saveAnswer($idSoalSiswa, $jawaban, $jawabanAlias, $ragu);

        if ($saved) {
            $this->response->json([
                'status' => 'success',
                'id_soal_siswa' => $idSoalSiswa,
                'ragu' => $ragu,
                'time' => date('H:i:s'),
            ]);
        } else {
            $this->response->json(['status' => 'error', 'message' => 'Gagal menyimpan jawaban.'], 500);
        }
    }

    /**
     * AJAX endpoint to record tab violation (Anti-Cheat).
     */
    public function logViolation(): void
    {
        $siswa = auth_siswa();
        if (!$siswa) {
            $this->response->json(['status' => 'error'], 401);
            return;
        }

        $idJadwal = (int)$this->request->input('id_jadwal', 0);
        if ($idJadwal <= 0) {
            $this->response->json(['status' => 'error'], 400);
            return;
        }

        $violations = $this->durasiModel->recordViolation($idJadwal, (int)$siswa['id_siswa']);

        $this->response->json([
            'status' => 'success',
            'violations' => $violations,
            'message' => 'Pelanggaran telah dicatat oleh sistem pengawas.',
        ]);
    }

    /**
     * Finish exam manually by student.
     */
    public function finish(int $idJadwal): void
    {
        $siswa = auth_siswa();
        if (!$siswa) {
            $this->redirect('/login');
            return;
        }

        $idSiswa = (int)$siswa['id_siswa'];

        // 1. Mark exam finished in cbt_durasi_siswa
        $this->durasiModel->finishExam($idJadwal, $idSiswa);

        // 2. Automatically grade all 5 AKM question types
        $this->grader->gradeExam($idJadwal, $idSiswa);

        set_flash('success', 'Ujian telah berhasil diselesaikan. Terima kasih atas partisipasi Anda!');
        $this->redirect('/student/cbt/selesai/' . $idJadwal);
    }

    /**
     * Exam finished confirmation page.
     */
    public function selesai(int $idJadwal): void
    {
        $siswa = auth_siswa();
        if (!$siswa) {
            $this->redirect('/login');
            return;
        }

        $idSiswa = (int)$siswa['id_siswa'];
        $jadwal = $this->jadwalModel->getDetail($idJadwal);
        $session = $this->durasiModel->getSession($idJadwal, $idSiswa);
        $nilai = $this->nilaiModel->getScore($idJadwal, $idSiswa);

        $this->render('student/cbt/selesai', [
            'title' => 'Ujian Selesai — DADO CBT',
            'siswa' => $siswa,
            'jadwal' => $jadwal,
            'session' => $session,
            'nilai' => $nilai,
        ], 'layouts/master');
    }
}

