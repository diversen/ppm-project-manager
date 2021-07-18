<?php declare (strict_types = 1);

namespace Pebble;

use Pebble\DBInstance;

/**
 * A simple authentication class based on a single database table
 */
class Auth
{
    /**
     * Authenticate a against database auth table
     */
    public function authenticate(string $email, string $password): array
    {

        $sql = 'SELECT * FROM auth WHERE email = ? AND verified = 1 AND locked = 0';
        $row = DBInstance::get()->prepareFetch($sql, [$email]);

        if (!empty($row) && password_verify($password, $row['password_hash'])) {
            return $row;
        }

        return [];
    }

    private function getRandom($len_bytes)
    {
        $random = bin2hex(random_bytes($len_bytes));
        return $random;
    }

    /**
     * Create a user using an email and a password
     */
    public function create(string $email, string $password): bool
    {

        $db = DBInstance::get();
        $random = $this->getRandom(32);

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
    public function verifyKey(string $key): bool
    {

        $db = DBInstance::get();

        $row = $this->getByWhere(['random' => $key]);

        if (!empty($row)) {

            $new_key = $this->getRandom(32);
            $sql = "UPDATE auth SET `verified` = 1, `random` = ? WHERE id= ? ";
            return $db->prepareExecute($sql, [$new_key, $row['id']]);

        } else {
            return false;
        }
    }

    /**
     * Check if an email is verified
     */
    public function isVerified(string $email): bool
    {
        $auth_row = $this->getByWhere(['verified' => 1, 'email' => $email]);
        if (empty($auth_row)) {
            return false;
        }
        return true;
    }

    /**
     * Get auth row by 'where' condition ['id' => 10, 'random' => 'random key of sorts']
     */
    public function getByWhere($where)
    {
        $db = DBInstance::get();
        return $db->getOne('auth', $where);

    }

    /**
     * Update 'password', actually the 'password_hash', and the random key bu auth 'id'
     */
    public function updatePassword(string $id, string $password): bool
    {

        $db = DBInstance::get();
        $random = $this->getRandom(32);

        $options = [
            'cost' => 12,
        ];

        $password_hash = password_hash($password, PASSWORD_BCRYPT, $options);

        $sql = "UPDATE auth SET `password_hash` = ?, `random` = ? WHERE id = ?";
        return $db->prepareExecute($sql, [$password_hash, $random, $id]);

    }

    /**
     * Get current users auth row from $_COOKIE['auth']
     */
    private function getValidCookieRow(): array
    {

        if (isset($_COOKIE['auth'])) {

            $sql = "SELECT * FROM auth_cookie WHERE cookie_id = ?";
            $row = DBInstance::get()->prepareFetch($sql, [$_COOKIE['auth']]);
            return $row;
        }

        return [];
    }

    /**
     * Check if a user has a valid auth cookie by searching the auth_cookie table
     * by $_COOKIE['auth']
     * @return boolean
     */
    public function isAuthenticated(): bool
    {
        $auth = $this->getValidCookieRow();
        if (empty($auth)) {
            return false;
        }
        return true;
    }

    /**
     * Get a auth id by checking the auth_cookie table for a $_COOKIE['auth'] match
     */
    public function getAuthId(): string
    {

        $auth_cookie_row = $this->getValidCookieRow();
        if (empty($auth_cookie_row)) {
            return "0";
        }
        return $auth_cookie_row['auth_id'];
    }

    /**
     * Unsets current auth cookie. This will log out the user
     */
    public function unlinkCurrentCookie()
    {

        if (isset($_COOKIE['auth'])) {

            // Delete current cookie
            $sql = "DELETE FROM auth_cookie WHERE cookie_id = ?";
            DBInstance::get()->prepareExecute($sql, [$_COOKIE['auth']]);

        }
    }

    /**
     * Unset all 'auth_cookies' across different devices
     */
    public function unlinkAllCookies($auth_id): bool
    {

        $sql = "DELETE FROM auth_cookie WHERE auth_id = ?";
        return DBInstance::get()->prepareExecute($sql, [$auth_id]);

    }

    /**
     * Generate a random string. Set this as the auth cookie.
     * Save the random string in database with the auth id.
     */
    private function setCookie(array $auth, int $expires): bool
    {

        $random = $this->getRandom(32);
        $res = $this->setBrowserCookie('auth', $random, $expires);

        if ($res) {

            $db = DBInstance::get();
            $sql = "INSERT INTO auth_cookie (`cookie_id`, `auth_id`, `expires`) VALUES (?, ?, ?) ";
            return $db->prepareExecute($sql, [$random, $auth['id'], $expires]);
        }
        return false;
    }

    /**
     * Set session cookie. It is actualy not a session cookie, because session cookies are not
     * Are not reliable across browsers 
     */
    public function setSessionCookie(array $auth): bool
    {
        $cookie_seconds = Config::get('Auth.cookie_seconds');
        if ($cookie_seconds == 0) {
            $expires = 0;
        } else {
            $expires = time() + Config::get('Auth.cookie_seconds');
        }

        return $this->setCookie($auth, $expires);
    }

    /**
     * Set a cookie that can last over many days or years
     * @param array $auth
     */
    public function setPermanentCookie(array $auth): bool
    {
        $expires = time() + Config::get('Auth.cookie_seconds_permanent');
        return $this->setCookie($auth, $expires);
    }

    private function isCli()
    {
        if (php_sapi_name() === 'cli') {
            return true;
        }
        return false;
    }

    /**
     * Set a cookie with key value and expire time
     */
    private function setBrowserCookie(string $key, string $value, int $time)
    {

        $auth_settings = Config::getSection('Auth');

        $path = $auth_settings['cookie_path'];
        $domain = $auth_settings['cookie_domain'];
        $secure = $auth_settings['cookie_secure'];
        $http_only = $auth_settings['cookie_http'];

        // Little hack for unit testing
        if ($this->isCli()) {
            $_COOKIE['auth'] = $value;
            return true;
        }

        return setcookie($key, $value, $time, $path, $domain, $secure, $http_only);

    }

    /**
     * @return \Pebble\Auth
     */
    public static function getInstance() {
        static $instance;
        if (!$instance) {
            $instance = new Auth();
        }

        return $instance;
    }
}
