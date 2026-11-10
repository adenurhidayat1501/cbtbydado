<?php

namespace App\Controllers\Master;

use App\Core\Controller;
use App\Models\MasterSmt;
use App\Models\MasterTp;

class TpSmtController extends Controller
{
    private MasterTp $tpModel;
    private MasterSmt $smtModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->tpModel = new MasterTp();
        $this->smtModel = new MasterSmt();
    }

    public function index(): void
    {
        $tpList = $this->tpModel->all('tahun DESC');
        $smtList = $this->smtModel->all('id_smt ASC');
        $activeTp = $this->tpModel->getActive();
        $activeSmt = $this->smtModel->getActive();

        $this->render('master/tp_smt/index', [
            'title' => 'Tahun Pelajaran & Semester — DADO CBT',
            'tpList' => $tpList,
            'smtList' => $smtList,
            'activeTp' => $activeTp,
            'activeSmt' => $activeSmt,
        ]);
    }

    public function storeTp(): void
    {
        $tahun = trim((string)$this->request->input('tahun', ''));

        if (empty($tahun)) {
            $this->setFlash('error', 'Tahun Pelajaran tidak boleh kosong (contoh: 2026/2027).');
            $this->redirect('/master/tp-smt');
            return;
        }

        // Check duplicate
        $exist = $this->tpModel->first(['tahun' => $tahun]);
        if ($exist) {
            $this->setFlash('error', "Tahun Pelajaran {$tahun} sudah ada di database.");
            $this->redirect('/master/tp-smt');
            return;
        }

        $active = (int)$this->request->input('active', 0);
        if ($active === 1) {
            $this->tpModel->execute("UPDATE master_tp SET active = 0");
        }

        $this->tpModel->insert([
            'tahun' => $tahun,
            'active' => $active,
        ]);

        $this->setFlash('success', "Tahun Pelajaran {$tahun} berhasil ditambahkan.");
        $this->redirect('/master/tp-smt');
    }

    public function activateTp(string $id): void
    {
        $idTp = (int)$id;
        $tp = $this->tpModel->find($idTp);

        if (!$tp) {
            $this->setFlash('error', 'Data Tahun Pelajaran tidak ditemukan.');
            $this->redirect('/master/tp-smt');
            return;
        }

        $this->tpModel->setActive($idTp);
        $this->setFlash('success', "Tahun Pelajaran {$tp['tahun']} berhasil diaktifkan secara global.");
        $this->redirect('/master/tp-smt');
    }

    public function deleteTp(string $id): void
    {
        $idTp = (int)$id;
        $tp = $this->tpModel->find($idTp);

        if (!$tp) {
            $this->setFlash('error', 'Data Tahun Pelajaran tidak ditemukan.');
            $this->redirect('/master/tp-smt');
            return;
        }

        if ((int)$tp['active'] === 1) {
            $this->setFlash('error', 'Tidak dapat menghapus Tahun Pelajaran yang sedang aktif.');
            $this->redirect('/master/tp-smt');
            return;
        }

        $this->tpModel->delete($idTp);
        $this->setFlash('success', "Tahun Pelajaran {$tp['tahun']} berhasil dihapus.");
        $this->redirect('/master/tp-smt');
    }

    public function activateSmt(string $id): void
    {
        $idSmt = (int)$id;
        $smt = $this->smtModel->find($idSmt);

        if (!$smt) {
            $this->setFlash('error', 'Data Semester tidak ditemukan.');
            $this->redirect('/master/tp-smt');
            return;
        }

        $this->smtModel->setActive($idSmt);
        $this->setFlash('success', "Semester {$smt['nama_smt']} ({$smt['smt']}) berhasil diaktifkan secara global.");
        $this->redirect('/master/tp-smt');
    }
}

