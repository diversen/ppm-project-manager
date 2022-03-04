<?php declare (strict_types = 1);

namespace App\Account;

use Pebble\CSRF;
use Diversen\Lang;
use App\AppMain;

class Validate {


    public function __construct() {
        $this->db = (new AppMain())->getDB();
    }
    public function postLogin() {
        $response = ['error' => true];

        if (!$this->token()) {
            http_response_code(403);
            $response['message'] = Lang::translate('Invalid Request. We will look in to this');
            return $response;
        }

        $response['error'] = false;
        return $response;

    }
    
    /**
     * Get 'Auth' row by email
     */
    public function getByEmail(string $email) : array
    {
        $sql = "SELECT * FROM auth WHERE email = ?";
        $row = $this->db->prepareFetch($sql, [$email]);
        return $row;
    }

    /**
     * Validate if two passwords do match
     */
    public function passwordsMatch(string $password, string $password_2) : bool
    {
        if ($password === $password_2) {
            return true;
        }
        return false;
    }

    /**
     * Does an email exists. Needs to be unique
     */
    public function emailExists(string $email) : bool
    {

        $row = $this->getByEmail($email);
        if (!empty($row)) {
            return true;
        }
        return false;

    }

    /**
     * Validate password strenth
     */
    public function passwordStrength($password) : bool
    {
        if (mb_strlen($password) < 7) {
            return false;
        }
        return true;
    }

    private function token() {
        $csrf = new CSRF();
        if (!$csrf->validateToken()) {
            return false;
        }
        return true;
    }

    /**
     * Validate signup form from $_POST
     */
    public function postSignup() : array
    {
        
        $response = ['error' => true];

        if (!$this->token()) {
            http_response_code(403);
            $response['message'] = Lang::translate('Invalid Request. We will look in to this');
            return $response;
        }

        if ($this->emailExists($_POST['email'])) {
            $response['message'] = Lang::translate('E-mail does already exists');
            return $response;
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $response['message'] = Lang::translate('Please enter a valid E-mail');
            return $response;
        }

        $res = $this->passwordsMatch($_POST['password'], $_POST['password_2']);
        if (!$res) {
            $response['message'] = Lang::translate('Passwords does not match');
            return $response;
        }

        $res = $this->passwordStrength($_POST['password']);
        if (!$res) {
            $response['message'] = Lang::translate('Passwords should be at least 7 chars long');
            return $response;
        }

        if (mb_strtolower($_POST['captcha']) != mb_strtolower($_SESSION['captcha_phrase'])) {
            $response['message'] = Lang::translate('Image text does not match');
            return $response;
        }

        $response['error'] = false;
        return $response;
    }

    /**
     * Validate passwords from $_POST
     */
    public function passwords(): array {

        $csrf = new CSRF();

        $response = ['error' => true];

        if (!$csrf->validateToken()) {
            $response['message'] = Lang::translate('Invalid Request. We will look in to this');
            return $response;
        }

        $res = $this->passwordsMatch($_POST['password'], $_POST['password_2']);
        if (!$res) {
            $response['message'] = Lang::translate('Passwords does not match');
            return $response;
        }

        $res = $this->passwordStrength($_POST['password']);
        if (!$res) {
            $response['message'] = Lang::translate('Passwords should be at least 7 chars long');
            return $response;
        }

        $response['error'] = false;
        return $response;
    }
}

