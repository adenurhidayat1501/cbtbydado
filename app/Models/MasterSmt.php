<?php

namespace App\Models;

use App\Core\Model;

class MasterSmt extends Model
{
    protected string $table = 'master_smt';
    protected string $primaryKey = 'id_smt';

    public function getActive(): ?array
    {
        return $this->first(['active' => 1]);
    }

    public function setActive(int $idSmt): bool
    {
        $this->execute("UPDATE master_smt SET active = 0");
        return $this->update($idSmt, ['active' => 1]);
    }
}

