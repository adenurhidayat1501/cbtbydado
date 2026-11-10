<?php

namespace App\Controllers\Elearning;

use App\Core\Controller;
use App\Models\KelasMateri;
use App\Models\KelasJadwalMateri;
use App\Models\KelasMateriKomentar;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterGuru;

class MateriController extends Controller
{
    private KelasMateri $materiModel;
    private KelasJadwalMateri $kjmModel;
    private KelasMateriKomentar $komentarModel;
    private MasterKelas $kelasModel;
    private MasterMapel $mapelModel;
    private MasterGuru $guruModel;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->materiModel = new KelasMateri();
        $this->kjmModel = new KelasJadwalMateri();
        $this->komentarModel = new KelasMateriKomentar();
        $this->kelasModel = new MasterKelas();
        $this->mapelModel = new MasterMapel();
        $this->guruModel = new MasterGuru();
    }

    /**
     * List all materials and tasks with role isolation.
     */
    public function index(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $role = auth_role();
        $currentGuru = auth_guru();
        $filterGuruId = null;

        if ($role === 'guru') {
            if (!$currentGuru) {
                set_flash('error', 'Data profil guru tidak ditemukan.');
                $this->redirect('/dashboard');
                return;
            }
            $filterGuruId = (int)$currentGuru['id_guru'];
        } else {
            // Admin can filter by teacher
            $queryGuru = (int)$this->request->query('guru_id', 0);
            if ($queryGuru > 0) {
                $filterGuruId = $queryGuru;
            }
        }

        $jenisFilter = $this->request->query('jenis');
        $jenis = ($jenisFilter !== null && $jenisFilter !== '') ? (int)$jenisFilter : null;
        $mapelFilter = (int)$this->request->query('mapel_id', 0);
        $idMapel = $mapelFilter > 0 ? $mapelFilter : null;
        $kelasFilter = (int)$this->request->query('kelas_id', 0);
        $idKelas = $kelasFilter > 0 ? $kelasFilter : null;

        $items = $this->materiModel->getAllByFilter($idTp, $idSmt, $filterGuruId, $jenis, $idMapel, $idKelas);

        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        if (empty($kelasList)) {
            $kelasList = $this->kelasModel->all('level_id ASC, nama_kelas ASC');
        }

        $mapelList = $this->mapelModel->where(['status' => 1], 'nama_mapel ASC');
        $guruList = ($role === 'admin') ? $this->guruModel->where(['status' => 1], 'nama_guru ASC') : [];

        $this->render('elearning/materi/index', [
            'title' => 'Materi & Tugas Belajar — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'items' => $items,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'guruList' => $guruList,
            'jenis' => $jenis,
            'selectedMapelId' => $idMapel,
            'selectedKelasId' => $idKelas,
            'selectedGuruId' => $filterGuruId,
            'role' => $role,
        ]);
    }

    /**
     * Show form to create new material or assignment.
     */
    public function create(): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $role = auth_role();
        $currentGuru = auth_guru();
        if ($role === 'guru' && !$currentGuru) {
            set_flash('error', 'Profil guru tidak ditemukan.');
            $this->redirect('/elearning/materi-tugas');
            return;
        }

        $jenis = (int)$this->request->query('jenis', 1); // 1 = Materi, 2 = Tugas
        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        if (empty($kelasList)) {
            $kelasList = $this->kelasModel->all('level_id ASC, nama_kelas ASC');
        }

        $mapelList = $this->mapelModel->where(['status' => 1], 'nama_mapel ASC');
        $guruList = ($role === 'admin') ? $this->guruModel->where(['status' => 1], 'nama_guru ASC') : [];

        $this->render('elearning/materi/create', [
            'title' => ($jenis === 1 ? 'Buat Materi Baru' : 'Buat Tugas Baru') . ' — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'jenis' => $jenis,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'guruList' => $guruList,
            'currentGuru' => $currentGuru,
            'role' => $role,
        ]);
    }

    /**
     * Store new material or assignment in database.
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
                $this->redirect('/elearning/materi-tugas');
                return;
            }
            $idGuru = (int)$currentGuru['id_guru'];
        } else {
            $idGuru = (int)$this->request->input('id_guru', 0);
            if ($idGuru <= 0 && $currentGuru) {
                $idGuru = (int)$currentGuru['id_guru'];
            }
        }

        $jenis = (int)$this->request->input('jenis', 1);
        $idMapel = (int)$this->request->input('id_mapel', 0);
        $judulMateri = trim((string)$this->request->input('judul_materi', ''));
        $isiMateri = (string)$this->request->input('isi_materi', '');
        $linkVideo = trim((string)$this->request->input('link_video', ''));
        $status = (int)$this->request->input('status', 1);

        $kelasSelected = $this->request->input('id_kelas', []);
        if (!is_array($kelasSelected)) {
            $kelasSelected = !empty($kelasSelected) ? [(int)$kelasSelected] : [];
        }

        if (empty($judulMateri) || $idMapel <= 0 || empty($kelasSelected) || $idGuru <= 0) {
            set_flash('error', 'Judul, Mata Pelajaran, Guru Pengampu, dan minimal 1 Kelas wajib diisi.');
            $this->redirect('/elearning/materi-tugas/create?jenis=' . $jenis);
            return;
        }

        $mapel = $this->mapelModel->find($idMapel);
        $mapelKode = $mapel['kode'] ?? 'MAPEL';
        $kodeMateri = trim((string)$this->request->input('kode_materi', ''));
        if (empty($kodeMateri)) {
            $kodeMateri = $this->materiModel->generateKode($jenis, $mapelKode);
        }

        // Handle uploaded files
        $uploadedFiles = [];
        $filesPayload = $_FILES['files'] ?? null;
        if ($filesPayload && !empty($filesPayload['name'][0])) {
            $targetDir = ($jenis === 1) ? __DIR__ . '/../../../public/uploads/materi/' : __DIR__ . '/../../../public/uploads/tugas/';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            $count = count($filesPayload['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($filesPayload['error'][$i] === UPLOAD_ERR_OK) {
                    $origName = $filesPayload['name'][$i];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'mp4', 'mp3', 'jpg', 'jpeg', 'png', 'zip'];
                    if (in_array($ext, $allowedExts)) {
                        $safeName = time() . '_' . mt_rand(1000, 9999) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($origName, PATHINFO_FILENAME)) . '.' . $ext;
                        if (move_uploaded_file($filesPayload['tmp_name'][$i], $targetDir . $safeName)) {
                            $uploadedFiles[] = [
                                'orig_name' => $origName,
                                'filename' => $safeName,
                                'path' => ($jenis === 1 ? 'uploads/materi/' : 'uploads/tugas/') . $safeName,
                                'size' => $filesPayload['size'][$i],
                            ];
                        }
                    }
                }
            }
        }

        $insertedId = $this->materiModel->createMateri([
            'id_tp' => $idTp,
            'id_smt' => $idSmt,
            'jenis' => $jenis,
            'kode_materi' => $kodeMateri,
            'id_guru' => $idGuru,
            'id_mapel' => $idMapel,
            'id_kelas' => $kelasSelected,
            'judul_materi' => $judulMateri,
            'isi_materi' => $isiMateri,
            'file' => $uploadedFiles,
            'link_video' => $linkVideo,
            'status' => $status,
        ]);

        if ($insertedId) {
            // Optional: If user also checked auto-schedule for today
            $autoSchedule = (int)$this->request->input('auto_schedule_today', 0);
            if ($autoSchedule === 1) {
                $today = date('Y-m-d');
                $this->kjmModel->scheduleMateri($idTp, $idSmt, $insertedId, $kelasSelected, $today);
            }

            $label = ($jenis === 1) ? 'Materi' : 'Tugas';
            set_flash('success', "{$label} '{$judulMateri}' berhasil disimpan.");
            $this->redirect('/elearning/materi-tugas?jenis=' . $jenis);
        } else {
            set_flash('error', 'Gagal menyimpan materi/tugas ke database.');
            $this->redirect('/elearning/materi-tugas/create?jenis=' . $jenis);
        }
    }

    /**
     * Show form to edit material or assignment.
     */
    public function edit(int $id): void
    {
        $tp = active_tp();
        $smt = active_smt();
        $idTp = (int)($tp['id_tp'] ?? 1);
        $idSmt = (int)($smt['id_smt'] ?? 1);

        $materi = $this->materiModel->getDetail($id);
        if (!$materi) {
            set_flash('error', 'Materi/Tugas tidak ditemukan.');
            $this->redirect('/elearning/materi-tugas');
            return;
        }

        $role = auth_role();
        $currentGuru = auth_guru();

        // Enforce teacher access isolation
        if ($role === 'guru') {
            if (!$currentGuru || (int)$materi['id_guru'] !== (int)$currentGuru['id_guru']) {
                set_flash('error', 'Akses Ditolak: Anda hanya berhak mengedit materi/tugas yang Anda buat sendiri.');
                $this->redirect('/elearning/materi-tugas');
                return;
            }
        }

        $kelasList = $this->kelasModel->getByPeriod($idTp, $idSmt);
        if (empty($kelasList)) {
            $kelasList = $this->kelasModel->all('level_id ASC, nama_kelas ASC');
        }

        $mapelList = $this->mapelModel->where(['status' => 1], 'nama_mapel ASC');
        $guruList = ($role === 'admin') ? $this->guruModel->where(['status' => 1], 'nama_guru ASC') : [];

        $this->render('elearning/materi/edit', [
            'title' => 'Edit ' . ((int)$materi['jenis'] === 1 ? 'Materi' : 'Tugas') . ' — DADO CBT',
            'tp' => $tp,
            'smt' => $smt,
            'materi' => $materi,
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'guruList' => $guruList,
            'currentGuru' => $currentGuru,
            'role' => $role,
        ]);
    }

    /**
     * Update material/assignment preserving old attachments unless explicitly removed.
     */
    public function update(int $id): void
    {
        $materi = $this->materiModel->getDetail($id);
        if (!$materi) {
            set_flash('error', 'Materi/Tugas tidak ditemukan.');
            $this->redirect('/elearning/materi-tugas');
            return;
        }

        $role = auth_role();
        $currentGuru = auth_guru();

        // Enforce teacher access isolation
        if ($role === 'guru') {
            if (!$currentGuru || (int)$materi['id_guru'] !== (int)$currentGuru['id_guru']) {
                set_flash('error', 'Akses Ditolak: Anda tidak memiliki izin memperbarui materi/tugas ini.');
                $this->redirect('/elearning/materi-tugas');
                return;
            }
        }

        $judulMateri = trim((string)$this->request->input('judul_materi', ''));
        $isiMateri = (string)$this->request->input('isi_materi', '');
        $idMapel = (int)$this->request->input('id_mapel', (int)$materi['id_mapel']);
        $linkVideo = trim((string)$this->request->input('link_video', ''));
        $status = (int)$this->request->input('status', 1);

        $kelasSelected = $this->request->input('id_kelas', []);
        if (!is_array($kelasSelected)) {
            $kelasSelected = !empty($kelasSelected) ? [(int)$kelasSelected] : [];
        }

        if (empty($judulMateri) || $idMapel <= 0 || empty($kelasSelected)) {
            set_flash('error', 'Judul, Mata Pelajaran, dan minimal 1 Kelas wajib diisi.');
            $this->redirect('/elearning/materi-tugas/edit/' . $id);
            return;
        }

        // Manage files: Keep existing selected files
        $keptFiles = $this->request->input('keep_files', []);
        $existingFiles = $materi['file_list'] ?? [];
        $mergedFiles = [];

        foreach ($existingFiles as $f) {
            if (in_array($f['filename'], (array)$keptFiles)) {
                $mergedFiles[] = $f;
            } else {
                // If removed, delete file from disk
                $targetFile = __DIR__ . '/../../../public/' . ltrim($f['path'], '/');
                if (file_exists($targetFile)) {
                    @unlink($targetFile);
                }
            }
        }

        // Process newly uploaded files
        $filesPayload = $_FILES['files'] ?? null;
        if ($filesPayload && !empty($filesPayload['name'][0])) {
            $targetDir = ((int)$materi['jenis'] === 1) ? __DIR__ . '/../../../public/uploads/materi/' : __DIR__ . '/../../../public/uploads/tugas/';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            $count = count($filesPayload['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($filesPayload['error'][$i] === UPLOAD_ERR_OK) {
                    $origName = $filesPayload['name'][$i];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'mp4', 'mp3', 'jpg', 'jpeg', 'png', 'zip'];
                    if (in_array($ext, $allowedExts)) {
                        $safeName = time() . '_' . mt_rand(1000, 9999) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($origName, PATHINFO_FILENAME)) . '.' . $ext;
                        if (move_uploaded_file($filesPayload['tmp_name'][$i], $targetDir . $safeName)) {
                            $mergedFiles[] = [
                                'orig_name' => $origName,
                                'filename' => $safeName,
                                'path' => ((int)$materi['jenis'] === 1 ? 'uploads/materi/' : 'uploads/tugas/') . $safeName,
                                'size' => $filesPayload['size'][$i],
                            ];
                        }
                    }
                }
            }
        }

        $payload = [
            'id_mapel' => $idMapel,
            'id_kelas' => $kelasSelected,
            'judul_materi' => $judulMateri,
            'isi_materi' => $isiMateri,
            'link_video' => $linkVideo,
            'status' => $status,
            'file' => $mergedFiles,
        ];

        $updated = $this->materiModel->updateMateri($id, $payload);

        if ($updated) {
            set_flash('success', 'Perubahan materi/tugas berhasil disimpan.');
            $this->redirect('/elearning/materi-tugas?jenis=' . $materi['jenis']);
        } else {
            set_flash('error', 'Gagal memperbarui materi/tugas.');
            $this->redirect('/elearning/materi-tugas/edit/' . $id);
        }
    }

    /**
     * Delete material/assignment with access isolation check and file cleanup.
     */
    public function delete(int $id): void
    {
        $materi = $this->materiModel->getDetail($id);
        if (!$materi) {
            set_flash('error', 'Materi/Tugas tidak ditemukan.');
            $this->redirect('/elearning/materi-tugas');
            return;
        }

        $role = auth_role();
        $currentGuru = auth_guru();

        if ($role === 'guru') {
            if (!$currentGuru || (int)$materi['id_guru'] !== (int)$currentGuru['id_guru']) {
                set_flash('error', 'Akses Ditolak: Anda tidak diizinkan menghapus materi/tugas ini.');
                $this->redirect('/elearning/materi-tugas');
                return;
            }
        }

        // Clean up attached files from disk
        if (!empty($materi['file_list'])) {
            foreach ($materi['file_list'] as $f) {
                $filePath = __DIR__ . '/../../../public/' . ltrim($f['path'], '/');
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        $deleted = $this->materiModel->delete($id);

        if ($deleted) {
            set_flash('success', 'Materi/Tugas berhasil dihapus.');
        } else {
            set_flash('error', 'Gagal menghapus materi/tugas.');
        }

        $this->redirect('/elearning/materi-tugas?jenis=' . $materi['jenis']);
    }

    /**
     * View detail of material/task with comments thread.
     */
    public function detail(int $id): void
    {
        $materi = $this->materiModel->getDetail($id);
        if (!$materi) {
            set_flash('error', 'Materi/Tugas tidak ditemukan.');
            $this->redirect('/elearning/materi-tugas');
            return;
        }

        $comments = $this->komentarModel->getKomentar($id);

        $this->render('elearning/materi/detail', [
            'title' => 'Detail ' . ((int)$materi['jenis'] === 1 ? 'Materi' : 'Tugas') . ' — DADO CBT',
            'materi' => $materi,
            'comments' => $comments,
            'role' => auth_role(),
        ]);
    }

    /**
     * Post a comment from teacher/admin view.
     */
    public function postKomentar(int $id): void
    {
        $komentar = trim((string)$this->request->input('komentar', ''));
        $userId = auth_id();

        if (empty($komentar) || !$userId) {
            set_flash('error', 'Komentar tidak boleh kosong.');
            $this->redirect('/elearning/materi-tugas/detail/' . $id);
            return;
        }

        $this->komentarModel->addKomentar($id, $userId, $komentar);
        set_flash('success', 'Komentar berhasil dikirim.');
        $this->redirect('/elearning/materi-tugas/detail/' . $id);
    }
}
