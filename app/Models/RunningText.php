<?php

namespace App\Models;

use App\Core\Model;

class RunningText extends Model
{
    protected string $table = 'running_text';
    protected string $primaryKey = 'id_text';

    public function getActiveList(?string $role = null): array
    {
        $sql = "SELECT * FROM running_text WHERE active = 1";
        $params = [];

        if ($role !== null) {
            $sql .= " AND tujuan IN (?, 'semua')";
            $params[] = $role;
        }

        $sql .= " ORDER BY id_text DESC";
        return $this->query($sql, $params);
    }
}

