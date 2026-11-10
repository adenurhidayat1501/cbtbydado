<?php

namespace App\Controllers\Cbt;

use App\Core\Controller;
use App\Models\CbtToken;

class TokenController extends Controller
{
    private CbtToken $tokenModel;

    public function __construct(?\App\Core\Request $request = null, ?\App\Core\Response $response = null)
    {
        parent::__construct($request, $response);
        $this->tokenModel = new CbtToken();
    }

    /**
     * Display CBT Token management view.
     */
    public function index(): void
    {
        $tokenData = $this->tokenModel->getActiveToken();
        if (!$tokenData) {
            $this->tokenModel->generateNewToken();
            $tokenData = $this->tokenModel->getActiveToken();
        }

        $this->render('cbt/token/index', [
            'title' => 'Token Ujian CBT — DADO CBT',
            'tokenData' => $tokenData,
        ]);
    }

    /**
     * Generate new CBT token (AJAX or normal POST).
     */
    public function generate(): void
    {
        $newToken = $this->tokenModel->generateNewToken();

        if ($this->request->isAjax()) {
            $this->response->json([
                'status' => 'success',
                'token' => $newToken,
                'updated_at' => date('d-m-Y H:i:s'),
                'message' => 'Token ujian baru berhasil digenerate!',
            ]);
            return;
        }

        set_flash('success', "Token ujian baru '{$newToken}' berhasil dirilis.");
        $this->redirect('/cbt/token');
    }

    /**
     * Update auto-refresh interval setting.
     */
    public function updateSetting(): void
    {
        $tokenData = $this->tokenModel->getActiveToken();
        if (!$tokenData) {
            $this->redirect('/cbt/token');
            return;
        }

        $auto = (int)$this->request->input('auto', 0);
        $jarak = max(5, min(120, (int)$this->request->input('jarak', 15)));

        $this->tokenModel->update($tokenData['id_token'], [
            'auto' => $auto,
            'jarak' => $jarak,
        ]);

        set_flash('success', 'Pengaturan auto-refresh token berhasil diperbarui.');
        $this->redirect('/cbt/token');
    }
}
