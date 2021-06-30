<?php

namespace App\Google;

use \Pebble\Config;

class GoogleUtils {

    public function getClient () {

        $jwt = new \Firebase\JWT\JWT;
        $jwt::$leeway = 5; // adjust this value

        $client = new \Google_Client(['jwt' => $jwt]);
        $client->setAuthConfig(Config::get('Google.auth_config'));
        $client->addScope('email');

        $redirect_uri = Config::get('App.server_scheme') .  '://' . Config::get('App.server_name') . '/google';
        $client->setRedirectUri($redirect_uri);

        return $client;
    }

    public function getAuthUrl () {

        $client = $this->getClient();
        return $client->createAuthUrl();
    }
}
