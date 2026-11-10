<?php

namespace App\Models;

use App\Core\Model;

class CbtToken extends Model
{
    protected string $table = 'cbt_token';
    protected string $primaryKey = 'id_token';

    public function getActiveToken(): ?array
    {
        $rows = $this->all('id_token ASC');
        return $rows[0] ?? null;
    }

    public function generateNewToken(int $length = 6): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $token = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[random_int(0, $max)];
        }

        $active = $this->getActiveToken();
        if ($active) {
            $this->update($active['id_token'], [
                'token' => $token,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->insert([
                'token' => $token,
                'auto' => 0,
                'jarak' => 15,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $token;
    }
}

