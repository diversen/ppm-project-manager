<?php

namespace App\TwoFactor;

use App\AppUtils;

class TwoFactorModel extends AppUtils
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUserRow(array $where): array
    {
        $row = $this->db->getOne('two_factor', $where);
        return $row;
    }

    public function getUserSecret(int $auth_id): ?string
    {
        $row = $this->getUserRow(['auth_id' => $auth_id]);
        if ($row) {
            return $row['secret'];
        }
    }

    public function isTwoFactorEnabled(int $auth_id): bool
    {
        if (empty($this->getUserRow(['auth_id' => $auth_id, 'verified' => '1']))) {
            return false;
        }
        return true;
    }

    public function verify(int $auth_id): bool
    {
        return $this->db->update('two_factor', ['verified' => '1'], ['auth_id' => $auth_id]);
    }

    public function create(int $auth_id, string $secret): bool
    {
        $this->delete($auth_id);
        return $this->db->insert('two_factor', ['auth_id' => $auth_id, 'secret' => $secret]);
    }

    public function delete(int $auth_id): bool
    {
        return $this->db->delete('two_factor', ['auth_id' => $auth_id]);
    }
}
