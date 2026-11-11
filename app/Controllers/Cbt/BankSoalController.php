<?php

namespace App\Controllers\Cbt;

use App\Core\Controller;
use App\Models\CbtBankSoal;
use App\Models\CbtSoal;
use App\Models\CbtJenis;
use App\Models\MasterMapel;
use App\Models\MasterKelas;
use App\Models\MasterGuru;
use App\Models\SettingSekolah;
use App\Services\WordImportParserService;
use App\Services\ExcelSoalImportService;

class BankSoalController extends Controller
{
    private CbtBankSoal $bankModel;
    private CbtSoal $soalModel;
    private CbtJenis $jenisModel;
    private MasterMapel $mapelModel;
    private MasterKelas $kelasModel;
    private MasterGuru $guruModel;
    private WordImportParserService $wordParser;
    private ExcelSoalImportService $excelParser;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->bankModel = new CbtBankSoal();
        $this->soalModel = new CbtSoal();
        $this->jenisModel = new CbtJenis();
        $this->mapelModel = new MasterMapel();
        $this->kelasModel = new MasterKelas();
        $this->guruModel = new MasterGuru();
        $this->wordParser = new WordImportParserService();
        $this->excelParser = new ExcelSoalImportService();
    }

    /**
     * List all Question Banks with role isolation.
     */
    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $role = auth_role();
        $currentGuru = auth_guru();
        $filterGuruId = ($role === 'guru' && $currentGuru) ? (int)$currentGuru['id_guru'] : null;

        if ($role === 'admin') {
            $queryGuru = (int)$this->request->query('guru_id', 0);
            if ($queryGuru > 0) {
                $filterGuruId = $queryGuru;
            }
        }

        $idMapel = (int)$this->request->query('mapel_id', 0) ?: null;
        $level = (int)$this->request->query('level', 0) ?: null;

        $banks = $this->bankModel->getAllByFilter($idTp, $idSmt, $filterGuruId, $idMapel, $level);

        $mapelList = $this->mapelModel->where(['status' => 1], 'nama_mapel ASC');
        $guruList = ($role === 'admin') ? $this->guruModel->where(['status' => 1], 'nama_guru ASC') : [];

        $this->render('cbt/bank_soal/index', [
            'title' => 'Bank Soal (5 AKM) — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'banks' => $banks,
            'mapelList' => $mapelList,
            'guruList' => $guruList,
            'selectedGuruId' => $filterGuruId,
            'selectedMapelId' => $idMapel,
            'selectedLevel' => $level,
            'role' => $role,
        ]);
    }

    /**
     * Show form to create new Question Bank.
     */
    public function create(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $role = auth_role();
        $currentGuru = auth_guru();

        $school = (new SettingSekolah())->getActive();
        $jenjang = (int)($school['jenjang'] ?? 2);
        // Default options: SD=3/4, SMP=4, SMA=5
        $defaultOpsi = ($jenjang === 1) ? 3 : (($jenjang === 2) ? 4 : 5);

        $mapelList = $this->mapelModel->where(['status' => 1], 'nama_mapel ASC');
        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        if (empty($kelasList)) {
            $kelasList = $this->kelasModel->all('level_id ASC, nama_kelas ASC');
        }

        $jenisList = $this->jenisModel->getAll();
        $guruList = ($role === 'admin') ? $this->guruModel->where(['status' => 1], 'nama_guru ASC') : [];

        $this->render('cbt/bank_soal/create', [
            'title' => 'Buat Bank Soal Baru — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'mapelList' => $mapelList,
            'kelasList' => $kelasList,
            'jenisList' => $jenisList,
            'guruList' => $guruList,
            'currentGuru' => $currentGuru,
            'defaultOpsi' => $defaultOpsi,
            'role' => $role,
        ]);
    }

    /**
     * Store new Question Bank in database.
     */
    public function store(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $role = auth_role();
        $currentGuru = auth_guru();

        if ($role === 'guru') {
            if (!$currentGuru) {
                set_flash('error', 'Profil guru tidak ditemukan.');
                $this->redirect('/cbt/bank-soal');
                return;
            }
            $idGuru = (int)$currentGuru['id_guru'];
        } else {
            $idGuru = (int)$this->request->input('bank_guru_id', 0);
            if ($idGuru <= 0 && $currentGuru) {
                $idGuru = (int)$currentGuru['id_guru'];
            }
        }

        $bankKode = strtoupper(trim((string)$this->request->input('bank_kode', '')));
        $bankMapelId = (int)$this->request->input('bank_mapel_id', 0);
        $bankLevel = (int)$this->request->input('bank_level', 7);
        $bankJenisId = (int)$this->request->input('bank_jenis_id', 0) ?: null;
        $opsi = (int)$this->request->input('opsi', 4);

        $kelasSelected = $this->request->input('bank_kelas', []);
        if (!is_array($kelasSelected)) {
            $kelasSelected = !empty($kelasSelected) ? [(int)$kelasSelected] : [];
        }

        if (empty($bankKode) || $bankMapelId <= 0 || $idGuru <= 0) {
            set_flash('error', 'Kode Bank Soal, Mata Pelajaran, dan Guru Pengampu wajib diisi.');
            $this->redirect('/cbt/bank-soal/create');
            return;
        }

        $payload = [
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'bank_jenis_id' => $bankJenisId,
            'bank_kode' => $bankKode,
            'bank_level' => $bankLevel,
            'bank_kelas' => json_encode(array_values(array_map('intval', $kelasSelected))),
            'bank_mapel_id' => $bankMapelId,
            'bank_guru_id' => $idGuru,
            'opsi' => $opsi,
            'tampil_pg' => (int)$this->request->input('tampil_pg', 0),
            'bobot_pg' => (int)$this->request->input('bobot_pg', 0),
            'tampil_kompleks' => (int)$this->request->input('tampil_kompleks', 0),
            'bobot_kompleks' => (int)$this->request->input('bobot_kompleks', 0),
            'tampil_jodohkan' => (int)$this->request->input('tampil_jodohkan', 0),
            'bobot_jodohkan' => (int)$this->request->input('bobot_jodohkan', 0),
            'tampil_isian' => (int)$this->request->input('tampil_isian', 0),
            'bobot_isian' => (int)$this->request->input('bobot_isian', 0),
            'tampil_esai' => (int)$this->request->input('tampil_esai', 0),
            'bobot_esai' => (int)$this->request->input('bobot_esai', 0),
            'status' => (int)$this->request->input('status', 1),
            'status_soal' => (int)$this->request->input('status_soal', 0),
        ];

        $insertedId = $this->bankModel->insert($payload);

        if ($insertedId) {
            set_flash('success', "Bank Soal '{$bankKode}' berhasil dibuat.");
            $this->redirect('/cbt/bank-soal/detail/' . $insertedId);
        } else {
            set_flash('error', 'Gagal menyimpan bank soal ke database.');
            $this->redirect('/cbt/bank-soal/create');
        }
    }

    /**
     * Show form to edit Question Bank settings.
     */
    public function edit(int $id): void
    {
        $bank = $this->bankModel->getDetail($id);
        if (!$bank) {
            set_flash('error', 'Bank Soal tidak ditemukan.');
            $this->redirect('/cbt/bank-soal');
            return;
        }

        $role = auth_role();
        $currentGuru = auth_guru();
        if ($role === 'guru' && (!$currentGuru || (int)$bank['bank_guru_id'] !== (int)$currentGuru['id_guru'])) {
            set_flash('error', 'Akses Ditolak: Anda tidak memiliki akses ke Bank Soal ini.');
            $this->redirect('/cbt/bank-soal');
            return;
        }

        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $mapelList = $this->mapelModel->where(['status' => 1], 'nama_mapel ASC');
        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        if (empty($kelasList)) {
            $kelasList = $this->kelasModel->all('level_id ASC, nama_kelas ASC');
        }

        $jenisList = $this->jenisModel->getAll();
        $guruList = ($role === 'admin') ? $this->guruModel->where(['status' => 1], 'nama_guru ASC') : [];

        $isLocked = $this->bankModel->isLocked($id);

        $this->render('cbt/bank_soal/edit', [
            'title' => 'Edit Bank Soal — DADO CBT',
            'bank' => $bank,
            'mapelList' => $mapelList,
            'kelasList' => $kelasList,
            'jenisList' => $jenisList,
            'guruList' => $guruList,
            'currentGuru' => $currentGuru,
            'isLocked' => $isLocked,
            'role' => $role,
        ]);
    }

    /**
     * Update Question Bank settings.
     */
    public function update(int $id): void
    {
        $bank = $this->bankModel->find($id);
        if (!$bank) {
            set_flash('error', 'Bank Soal tidak ditemukan.');
            $this->redirect('/cbt/bank-soal');
            return;
        }

        $role = auth_role();
        $currentGuru = auth_guru();
        if ($role === 'guru' && (!$currentGuru || (int)$bank['bank_guru_id'] !== (int)$currentGuru['id_guru'])) {
            set_flash('error', 'Akses Ditolak.');
            $this->redirect('/cbt/bank-soal');
            return;
        }

        $kelasSelected = $this->request->input('bank_kelas', []);
        if (!is_array($kelasSelected)) {
            $kelasSelected = !empty($kelasSelected) ? [(int)$kelasSelected] : [];
        }

        $payload = [
            'bank_kode' => strtoupper(trim((string)$this->request->input('bank_kode', $bank['bank_kode']))),
            'bank_mapel_id' => (int)$this->request->input('bank_mapel_id', $bank['bank_mapel_id']),
            'bank_level' => (int)$this->request->input('bank_level', $bank['bank_level']),
            'bank_jenis_id' => (int)$this->request->input('bank_jenis_id', 0) ?: null,
            'bank_kelas' => json_encode(array_values(array_map('intval', $kelasSelected))),
            'opsi' => (int)$this->request->input('opsi', $bank['opsi']),
            'tampil_pg' => (int)$this->request->input('tampil_pg', 0),
            'bobot_pg' => (int)$this->request->input('bobot_pg', 0),
            'tampil_kompleks' => (int)$this->request->input('tampil_kompleks', 0),
            'bobot_kompleks' => (int)$this->request->input('bobot_kompleks', 0),
            'tampil_jodohkan' => (int)$this->request->input('tampil_jodohkan', 0),
            'bobot_jodohkan' => (int)$this->request->input('bobot_jodohkan', 0),
            'tampil_isian' => (int)$this->request->input('tampil_isian', 0),
            'bobot_isian' => (int)$this->request->input('bobot_isian', 0),
            'tampil_esai' => (int)$this->request->input('tampil_esai', 0),
            'bobot_esai' => (int)$this->request->input('bobot_esai', 0),
            'status' => (int)$this->request->input('status', 1),
            'status_soal' => (int)$this->request->input('status_soal', 0),
        ];

        if ($role === 'admin') {
            $idGuru = (int)$this->request->input('bank_guru_id', 0);
            if ($idGuru > 0) {
                $payload['bank_guru_id'] = $idGuru;
            }
        }

        $this->bankModel->update($id, $payload);
        set_flash('success', 'Pengaturan Bank Soal berhasil diperbarui.');
        $this->redirect('/cbt/bank-soal/detail/' . $id);
    }

    /**
     * Delete Question Bank (with ongoing exam protection).
     */
    public function delete(int $id): void
    {
        $bank = $this->bankModel->find($id);
        if (!$bank) {
            set_flash('error', 'Bank Soal tidak ditemukan.');
            $this->redirect('/cbt/bank-soal');
            return;
        }

        if ($this->bankModel->isLocked($id)) {
            set_flash('error', 'Gagal: Bank Soal sedang digunakan dalam jadwal ujian yang aktif.');
            $this->redirect('/cbt/bank-soal');
            return;
        }

        $this->bankModel->delete($id);
        set_flash('success', 'Bank Soal berhasil dihapus.');
        $this->redirect('/cbt/bank-soal');
    }

    /**
     * Overview of questions inside a bank.
     */
    public function detail(int $id): void
    {
        $bank = $this->bankModel->getDetail($id);
        if (!$bank) {
            set_flash('error', 'Bank Soal tidak ditemukan.');
            $this->redirect('/cbt/bank-soal');
            return;
        }

        $questions = $this->soalModel->getByBank($id);
        $isLocked = $this->bankModel->isLocked($id);

        $this->render('cbt/bank_soal/detail', [
            'title' => 'Detail Bank Soal: ' . $bank['bank_kode'] . ' — DADO CBT',
            'bank' => $bank,
            'questions' => $questions,
            'isLocked' => $isLocked,
            'role' => auth_role(),
        ]);
    }

    /**
     * Editor Butir Soal 5 Tipe AKM.
     */
    public function editorSoal(int $idBank): void
    {
        $bank = $this->bankModel->getDetail($idBank);
        if (!$bank) {
            set_flash('error', 'Bank Soal tidak ditemukan.');
            $this->redirect('/cbt/bank-soal');
            return;
        }

        $editId = (int)$this->request->query('soal_id', 0);
        $editSoal = $editId > 0 ? $this->soalModel->find($editId) : null;
        $nextNomor = $editSoal ? (int)$editSoal['nomor_soal'] : $this->soalModel->getNextNomor($idBank);

        $selectedJenis = $editSoal ? (int)$editSoal['jenis'] : (int)$this->request->query('jenis', 1);

        $this->render('cbt/bank_soal/editor_soal', [
            'title' => 'Editor Soal AKM: ' . $bank['bank_kode'] . ' — DADO CBT',
            'bank' => $bank,
            'editSoal' => $editSoal,
            'nextNomor' => $nextNomor,
            'selectedJenis' => $selectedJenis,
            'role' => auth_role(),
        ]);
    }

    /**
     * Store or update question from editor.
     */
    public function storeSoal(int $idBank): void
    {
        $jenis = (int)$this->request->input('jenis', 1);
        $soalText = (string)$this->request->input('soal', '');
        $idSoal = (int)$this->request->input('id_soal', 0) ?: null;

        if (empty(trim($soalText))) {
            set_flash('error', 'Teks pertanyaan soal wajib diisi.');
            $this->redirect('/cbt/bank-soal/soal/' . $idBank . ($idSoal ? '?soal_id=' . $idSoal : ''));
            return;
        }

        $opsiA = $this->request->input('opsi_a');
        $opsiB = $this->request->input('opsi_b');
        $opsiC = $this->request->input('opsi_c');
        $opsiD = $this->request->input('opsi_d');
        $opsiE = $this->request->input('opsi_e');

        $jawaban = '';
        if ($jenis === 1) {
            // PG Tunggal: Huruf "A", "B", "C", "D", "E"
            $jawaban = strtoupper(trim((string)$this->request->input('jawaban_pg', 'A')));
        } elseif ($jenis === 2) {
            // PG Kompleks: JSON Array e.g. ["A", "C"]
            $pgkAnswers = $this->request->input('jawaban_pgk', []);
            $jawaban = is_array($pgkAnswers) ? array_values(array_map('strtoupper', $pgkAnswers)) : [];
        } elseif ($jenis === 3) {
            // Menjodohkan: Line matching JSON structure
            $premisKiri = $this->request->input('baris', []);
            $responKanan = $this->request->input('kolom', []);
            $linkMappings = $this->request->input('links', []);

            $jawaban = [
                'type' => 'line',
                'mode' => '1',
                'baris' => is_array($premisKiri) ? array_values($premisKiri) : [],
                'kolom' => is_array($responKanan) ? array_values($responKanan) : [],
                'links' => is_array($linkMappings) ? $linkMappings : [],
            ];
        } elseif ($jenis === 4) {
            // Isian Singkat: String
            $jawaban = trim((string)$this->request->input('jawaban_isian', ''));
        } elseif ($jenis === 5) {
            // Essai: Pedoman penilaian
            $jawaban = trim((string)$this->request->input('jawaban_esai', ''));
        }

        $nomorSoal = (int)$this->request->input('nomor_soal', 0);

        $saved = $this->soalModel->saveSoal($idBank, [
            'jenis' => $jenis,
            'nomor_soal' => $nomorSoal,
            'soal' => $soalText,
            'opsi_a' => $opsiA,
            'opsi_b' => $opsiB,
            'opsi_c' => $opsiC,
            'opsi_d' => $opsiD,
            'opsi_e' => $opsiE,
            'jawaban' => $jawaban,
            'tampilkan' => 1,
        ], $idSoal);

        if ($saved) {
            set_flash('success', 'Butir soal berhasil disimpan.');
        } else {
            set_flash('error', 'Gagal menyimpan butir soal.');
        }

        $this->redirect('/cbt/bank-soal/detail/' . $idBank);
    }

    /**
     * Delete a question item.
     */
    public function deleteSoal(int $idBank, int $idSoal): void
    {
        $deleted = $this->soalModel->deleteSoal($idSoal);
        if ($deleted) {
            set_flash('success', 'Butir soal berhasil dihapus.');
        } else {
            set_flash('error', 'Gagal menghapus butir soal.');
        }
        $this->redirect('/cbt/bank-soal/detail/' . $idBank);
    }

    /**
     * AJAX image upload for Summernote editor.
     */
    public function uploadImage(): void
    {
        $file = $_FILES['image'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->response->json(['status' => 'error', 'message' => 'Gagal mengunggah gambar.'], 400);
            return;
        }

        $targetDir = __DIR__ . '/../../../public/uploads/soal/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!in_array($ext, $allowed)) {
            $this->response->json(['status' => 'error', 'message' => 'Ekstensi file tidak diizinkan.'], 400);
            return;
        }

        $safeName = 'img_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $targetDir . $safeName)) {
            $url = base_url('uploads/soal/' . $safeName);
            $this->response->json(['status' => 'success', 'url' => $url]);
        } else {
            $this->response->json(['status' => 'error', 'message' => 'Gagal menyimpan berkas.'], 500);
        }
    }

    /**
     * Import questions from Word (.docx).
     */
    public function importWord(int $idBank): void
    {
        $file = $_FILES['file_word'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            set_flash('error', 'Pilih file Word (.docx) yang valid.');
            $this->redirect('/cbt/bank-soal/detail/' . $idBank);
            return;
        }

        try {
            $questions = $this->wordParser->parseDocx($file['tmp_name']);
            if (empty($questions)) {
                set_flash('error', 'Tidak ada butir soal yang berhasil dibaca dari dokumen Word. Pastikan format tabel sesuai template.');
                $this->redirect('/cbt/bank-soal/detail/' . $idBank);
                return;
            }

            $count = 0;
            foreach ($questions as $q) {
                $this->soalModel->saveSoal($idBank, [
                    'jenis' => $q['jenis'] ?? 1,
                    'nomor_soal' => $q['nomor'] ?? 0,
                    'soal' => $q['soal'] ?? '',
                    'opsi_a' => $q['opsi_a'] ?? null,
                    'opsi_b' => $q['opsi_b'] ?? null,
                    'opsi_c' => $q['opsi_c'] ?? null,
                    'opsi_d' => $q['opsi_d'] ?? null,
                    'opsi_e' => $q['opsi_e'] ?? null,
                    'jawaban' => $q['jawaban'] ?? 'A',
                    'tampilkan' => 1,
                ]);
                $count++;
            }

            set_flash('success', "Berhasil mengimpor {$count} butir soal dari dokumen Word.");
        } catch (\Throwable $e) {
            set_flash('error', 'Gagal memproses file Word: ' . $e->getMessage());
        }

        $this->redirect('/cbt/bank-soal/detail/' . $idBank);
    }

    /**
     * Import questions from Excel (.xlsx) / CSV.
     */
    public function importExcel(int $idBank): void
    {
        $file = $_FILES['file_excel'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            set_flash('error', 'Pilih file Excel (.xlsx / .csv) yang valid.');
            $this->redirect('/cbt/bank-soal/detail/' . $idBank);
            return;
        }

        try {
            $originalName = $file['name'] ?? null;
            $questions = $this->excelParser->parse($file['tmp_name'], $originalName);
            if (empty($questions)) {
                set_flash('error', 'Tidak ada butir soal yang berhasil dibaca dari file Excel/CSV. Pastikan format tabel sesuai template.');
                $this->redirect('/cbt/bank-soal/detail/' . $idBank);
                return;
            }

            $count = 0;
            foreach ($questions as $q) {
                $this->soalModel->saveSoal($idBank, [
                    'jenis' => $q['jenis'] ?? 1,
                    'nomor_soal' => $q['nomor'] ?? 0,
                    'soal' => $q['soal'] ?? '',
                    'opsi_a' => $q['opsi_a'] ?? null,
                    'opsi_b' => $q['opsi_b'] ?? null,
                    'opsi_c' => $q['opsi_c'] ?? null,
                    'opsi_d' => $q['opsi_d'] ?? null,
                    'opsi_e' => $q['opsi_e'] ?? null,
                    'jawaban' => $q['jawaban'] ?? 'A',
                    'tampilkan' => 1,
                ]);
                $count++;
            }

            set_flash('success', "Berhasil mengimpor {$count} butir soal dari berkas Excel/CSV.");
        } catch (\Throwable $e) {
            set_flash('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }

        $this->redirect('/cbt/bank-soal/detail/' . $idBank);
    }

    /**
     * Download Word question template (.docx).
     */
    public function downloadTemplateWord(): void
    {
        $this->wordParser->generateTemplateDocx();
    }

    /**
     * Download Excel question template (.csv).
     */
    public function downloadTemplateExcel(): void
    {
        $this->excelParser->downloadTemplate();
    }
}
