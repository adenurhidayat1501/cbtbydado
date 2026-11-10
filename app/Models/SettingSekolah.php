<?php

namespace App\Models;

use App\Core\Model;

class SettingSekolah extends Model
{
    protected string $table = 'setting_sekolah';
    protected string $primaryKey = 'id_setting';

    public function getActive(): ?array
    {
        $rows = $this->all('id_setting ASC');
        return $rows[0] ?? null;
    }
}

