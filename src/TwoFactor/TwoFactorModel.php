<?php

declare(strict_types=1);

namespace App\TwoFactor;

use App\AppUtils;
use Pebble\SessionTimed;
use Diversen\Lang;

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

    public function checkAndRedirect(int $auth_id, bool $json_response = true): bool {
        if ($this->isTwoFactorEnabled($auth_id)) {

            // Set session values to verify login
            $session_timed = new SessionTimed();
            $session_timed->setValue('auth_id_to_login', $auth_id, $this->config->get('TwoFactor.time_to_verify'));
            $session_timed->setValue('keep_login', isset($_POST['keep_login']), $this->config->get('TwoFactor.time_to_verify'));
            
            // Flash
            $this->flash->setMessage(Lang::translate('Verify your login.'), 'success', ['flash_remove' => true]);
            
            // Render json response
            if ($json_response) {
                $response['error'] = false;
                $response['redirect'] = '/twofactor/verify';
                $this->json->render($response);
            } else {
                header("Location: " . '/twofactor/verify');
            }
            
            return true;
        }

        return false;
    }

}
