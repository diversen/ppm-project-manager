<?php

declare(strict_types=1);

namespace App\Account;

use Diversen\Lang;
use App\AppUtils;
use Pebble\Exception\JSONException;

class Validate extends AppUtils
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Validate if two passwords do match
     */
    public function passwordsMatch(string $password, string $password_2): bool
    {
        if ($password === $password_2) {
            return true;
        }
        return false;
    }

    /**
     * Does an email exists. Needs to be unique
     */
    public function emailExists(string $email): bool
    {
        $row = $this->auth->getByWhere(['email' => $email]);
        if (!empty($row)) {
            return true;
        }
        return false;
    }

    /**
     * Validate password strenth
     */
    public function passwordStrength($password): bool
    {
        if (mb_strlen($password) < 7) {
            return false;
        }
        return true;
    }

    /**
     * Validate signup form from $_POST
     * Throw exception if not valid
     */
    public function postSignup(): void
    {
        if ($this->emailExists($_POST['email'])) {
            throw new JSONException(Lang::translate('E-mail does already exists'));
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new JSONException(Lang::translate('Please enter a valid E-mail'));
        }

        $res = $this->passwordsMatch($_POST['password'], $_POST['password_2']);
        if (!$res) {
            throw new JSONException(Lang::translate('Passwords does not match'));
        }

        $res = $this->passwordStrength($_POST['password']);
        if (!$res) {
            throw new JSONException(Lang::translate('Passwords should be at least 7 chars long'));
        }

        if (mb_strtolower($_POST['captcha']) != mb_strtolower($_SESSION['captcha_phrase'])) {
            throw new JSONException(Lang::translate('Image text does not match'));
        }
    }

    /**
     * Validate passwords from $_POST
     */
    public function passwords(): void
    {
        $res = $this->passwordsMatch($_POST['password'], $_POST['password_2']);
        if (!$res) {
            throw new JSONException(Lang::translate('Passwords does not match'));
        }

        $res = $this->passwordStrength($_POST['password']);
        if (!$res) {
            throw new JSONException(Lang::translate('Passwords should be at least 7 chars long'));
        }
    }
}
