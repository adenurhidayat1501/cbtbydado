<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    public function findByUsername(string $username): ?array
    {
        return $this->first(['username' => $username]);
    }

    public function updateSessionToken(int $userId, ?string $token): bool
    {
        return $this->update($userId, [
            'session_token' => $token,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updatePassword(int $userId, string $newPasswordHash): bool
    {
        return $this->update($userId, [
            'password' => $newPasswordHash,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

