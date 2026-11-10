<?php

namespace App\Models;

use App\Core\Model;

class MasterGuru extends Model
{
    protected string $table = 'master_guru';
    protected string $primaryKey = 'id_guru';

    public function findByUserId(int $userId): ?array
    {
        return $this->first(['id_user' => $userId]);
    }

    public function findByNip(string $nip): ?array
    {
        return $this->first(['nip' => $nip]);
    }
}

