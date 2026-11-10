<?php

namespace App\Models;

use App\Core\Model;

class CbtJenis extends Model
{
    protected string $table = 'cbt_jenis';
    protected string $primaryKey = 'id_jenis';

    /**
     * Get all exam types ordered by id.
     */
    public function getAll(): array
    {
        return $this->all('id_jenis ASC');
    }

    /**
     * Find exam type by its code e.g. 'PTS', 'PAS', 'PH'.
     */
    public function findByKode(string $kode): ?array
    {
        return $this->first(['kode_jenis' => strtoupper(trim($kode))]);
    }
}
