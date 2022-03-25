<?php

namespace App\Google;

use App\AppMain;

class GoogleUtils
{
    public function getClient()
    {
        $config = (new AppMain())->getConfig();

        $jwt = new \Firebase\JWT\JWT();
        $jwt::$leeway = 5; // adjust this value

        $client = new \Google_Client(['jwt' => $jwt]);
        $client->setAuthConfig($config->get('Google.auth_config'));
        $client->addScope('email');

        $redirect_uri = $config->get('App.server_scheme') .  '://' . $config->get('App.server_name') . '/google';
        $client->setRedirectUri($redirect_uri);

        return $client;
    }

    public function getAuthUrl()
    {
        $client = $this->getClient();
        return $client->createAuthUrl();
    }
}
