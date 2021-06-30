<?php declare(strict_types=1);

namespace Pebble;

use \Pebble\DBInstance;
use \Pebble\Random;

/**
 * A simple authentication class based on a single database table
 */
class Auth
{
    /**
     * Authenticate a against database auth table
     */
    public function authenticate(string $email, string $password) : array
    {

        $sql = 'SELECT * FROM auth WHERE email = ? AND verified = 1 AND locked = 0';
        $row = DBInstance::get()->prepareFetch($sql, [$email]);

        if (!empty($row) && password_verify($password, $row['password_hash'])) {
            return $row;
        }

        return [];
    }

    /**
     * Create a user using an email and a password
     */
    public function create(string $email, string $password) : bool
    {

        $db = DBInstance::get();
        $random = Random::generateRandomString(64);

        $options = [
            'cost' => 12,
        ];

        $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);

        $sql = "INSERT INTO auth (`email`, `password_hash`, `random`) VALUES(?, ?, ?)";
        return $db->prepareExecute($sql, [$email, $password_hash, $random]);

    }

    /**
     * Verify a auth row by a key. Set verified = 1 and generate a new key
     * if there is a match
     */
    public function verifyKey(string $key) : bool
    {
        
        $db = DBInstance::get();

        $row = $this->getByRandom($key);

        if (!empty($row)) {

            $new_key = Random::generateRandomString(64);
            $sql = "UPDATE auth SET `verified` = 1, `random` = ? WHERE id= ? ";
            return $db->prepareExecute($sql, [$new_key, $row['id']]);

        } else {
            return false;
        }
    }

    /**
     * Check if an email is verified
     */
    public function isVerified(string $email): bool {
        $db = DBInstance::get();
        $auth_row = $db->getOne('auth', ['verified' => 1, 'email' => $email]);
        if (empty($auth_row)) {
            return false;
        }
        return true;
    }

    /**
     * Get auth row by the random key
     */
    public function getByRandom (string $key): array {
        $db = DBInstance::get();
        $sql = "SELECT * FROM auth WHERE `random` = ?";
        $row = $db->prepareFetch($sql, [$key]);
        return $row;
    }

    /**
     * Get auth row by email
     */
    public function getByEmail(?string $email): array {
        $db = DBInstance::get();
        $sql = 'SELECT * FROM `auth` WHERE `email` = ?';
        return $db->prepareFetch($sql, [$email]);
    }

    /**
     * Update 'password', actually the 'password_hash', and the random key bu auth 'id'
     */
    public function updatePassword(string $id, string $password) : bool
    {

        $db = DBInstance::get();
        $random = Random::generateRandomString(64);

        $options = [
            'cost' => 12,
        ];

        $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);

        $sql = "UPDATE auth SET `password_hash` = ?, `random` = ? WHERE id = ?";
        return $db->prepareExecute($sql, [$password_hash, $random, $id]);

    }


    /**
     * Check if a user has a valid auth cookie by searching the auth_cookie table 
     * by $_COOKIE['auth']
     * @return boolean
     */
    public function isAuthenticated() : bool
    {
        if (isset($_COOKIE['auth'])) {
            $auth = $this->getAuthCookieFromDB();
            if (empty($auth)) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Check a 'auth' row from a auth id
     * @return array $row
     */
    public function getAuthRowFromAuthId(int $auth_id) : array
    {

        $db = DBInstance::get();
        $auth_row = $db->getOne('auth', ['id' => $auth_id]);
        return $auth_row;
    }

    /**
     * Get a auth id by checking the auth_cookie table for a $_COOKIE['auth'] match
     */
    public function getAuthId () : int {

        $auth_cookie_row = $this->getAuthCookieFromDB();
        if (empty($auth_cookie_row)) {
            return 0;
        }
        return (int)$auth_cookie_row['auth_id'];
    }

    /**
     * Unsets current auth cookie. This will log out the user
     */
    public function unsetCurrentAuthCookie()
    {

        if (isset($_COOKIE['auth'])) {

            // Delete current cookie
            $sql = "DELETE FROM auth_cookie WHERE cookie_id = ?";
            DBInstance::get()->prepareExecute($sql, [$_COOKIE['auth']]);

            // Unset auth cookie
            $this->setCookie("auth", "", time() - 3600);

        }
    }

    /**
     * Unset all 'auth_cookies' across different devices
     */
    public function unsetAllAuthCookies ($auth_id): bool {

        $sql = "DELETE FROM auth_cookie WHERE auth_id = ?";
        return DBInstance::get()->prepareExecute($sql, [$auth_id]);
    }

    /**
     * Generate a random string. Set this as the auth cookie. 
     * Save the random string in database with the auth id. 
     */
    public function setAuthCookieDB(array $auth, int $expires) : bool
    {

        $random = Random::generateRandomString(64);
        $res = $this->setCookie('auth', $random, $expires);

        if ($res) {

            $db = DBInstance::get();
            $sql = "INSERT INTO auth_cookie (`cookie_id`, `auth_id`) VALUES (?, ?) ";
            return $db->prepareExecute($sql, [$random, $auth['id']]);
        }
        return false;
    }

    /**
     * Set session cookie. This is a cookie with time 0 
     */
    public function setSessionAuthCookieDB (array $auth): bool {
        $expires = 0;
        return $this->setAuthCookieDB($auth, $expires);
    }

    /**
     * Set a cookie that can last over days
     * @param array $auth
     */
    public function setPermanentAuthCookieDB (array $auth): bool {
        $auth_settings = Config::getSection('Auth');
        $expires = time() + $this->getCookieTime($auth_settings['cookie_days']);
        return $this->setAuthCookieDB($auth, $expires);
    }

    /**
     * Get current users auth row from $_COOKIE['auth']
     */
    public function getAuthCookieFromDB() : array
    {

        if (isset($_COOKIE['auth'])) {

            $sql = "SELECT * FROM auth_cookie WHERE cookie_id = ?";
            $row = DBInstance::get()->prepareFetch($sql, [$_COOKIE['auth']]);
            return $row;
        }

        return [];
    }

    /**
     * Get cookie time in seconds from days
     */
    private function getCookieTime(int $days) : int
    {
        return $days * 60 * 60 * 24;
    }

    /**
     * Set a cookie with key value and expire time
     */
    private function setCookie (string $key, string $value, int $time) {

        $auth_settings = Config::getSection('Auth');

        $path = $auth_settings['cookie_path'];
        $domain = $auth_settings['cookie_domain'];
        $secure = $auth_settings['cookie_secure'];
        $http_only = $auth_settings['cookie_http'];

        return setcookie($key, $value, $time, $path, $domain, $secure, $http_only);
    }

    /**
     * Create a new Auth object
     */
    public static function factory() {
        return new self();
    }

    /**
     * Variable holding an instance object
     */
    public static $instance = null;

    /**
     * Get the instance object
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Auth();
        }

        return self::$instance;

    }
}
