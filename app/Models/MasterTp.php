<?php

namespace App\Models;

use App\Core\Model;

class MasterTp extends Model
{
    protected string $table = 'master_tp';
    protected string $primaryKey = 'id_tp';

    public function getActive(): ?array
    {
        return $this->first(['active' => 1]);
    }

    public function setActive(int $idTp): bool
    {
        $this->execute("UPDATE master_tp SET active = 0");
        return $this->update($idTp, ['active' => 1]);
    }
}

