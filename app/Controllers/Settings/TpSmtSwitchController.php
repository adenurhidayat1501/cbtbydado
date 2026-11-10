<?php

namespace App\Controllers\Settings;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\MasterSmt;
use App\Models\MasterTp;

class TpSmtSwitchController extends Controller
{
    private MasterTp $tpModel;
    private MasterSmt $smtModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->tpModel = new MasterTp();
        $this->smtModel = new MasterSmt();
    }

    /**
     * Switch active TP/SMT globally or in session
     */
    public function switch(): void
    {
        $idTp = (int)$this->request->input('id_tp');
        $idSmt = (int)$this->request->input('id_smt');
        $isGlobal = (int)$this->request->input('global', 0) === 1 && Auth::role() === 'admin';

        if ($idTp > 0) {
            if ($isGlobal) {
                $this->tpModel->setActive($idTp);
            }
            Session::set('active_tp_id', $idTp);
        }

        if ($idSmt > 0) {
            if ($isGlobal) {
                $this->smtModel->setActive($idSmt);
            }
            Session::set('active_smt_id', $idSmt);
        }

        $tp = active_tp();
        $smt = active_smt();

        $msg = sprintf(
            'Tahun Pelajaran & Semester aktif diubah menjadi: %s — Semester %s (%s).',
            $tp['tahun'] ?? '-',
            $smt['smt'] ?? '-',
            $smt['nama_smt'] ?? '-'
        );

        if ($this->request->isAjax()) {
            $this->json([
                'success' => true,
                'message' => $msg,
                'tp' => $tp,
                'smt' => $smt,
            ]);
            return;
        }

        $this->setFlash('success', $msg);
        $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
        $this->redirect($referer);
    }
}

