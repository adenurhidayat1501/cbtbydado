<?php

namespace App\Models;

use App\Core\Model;

class MasterMapel extends Model
{
    protected string $table = 'master_mapel';
    protected string $primaryKey = 'id_mapel';

    public function getByKelompok(string $kelompok): array
    {
        return $this->where(['kelompok' => $kelompok, 'status' => 1], 'urutan_tampil ASC, nama_mapel ASC');
    }

    public function getAllOrdered(): array
    {
        return $this->all('urutan_tampil ASC, kelompok ASC, nama_mapel ASC');
    }
}

