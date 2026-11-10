<?php

namespace App\Models;

use App\Core\Model;

class MasterJurusan extends Model
{
    protected string $table = 'master_jurusan';
    protected string $primaryKey = 'id_jurusan';

    public function getActive(): array
    {
        return $this->where(['status' => 1], 'nama_jurusan ASC');
    }
}

