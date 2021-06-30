<?php declare(strict_types=1);

namespace Pebble;

use \Gregwar\Captcha\CaptchaBuilder;

/**
 * Wrapper around Captcha
 */
class Captcha {

    /**
     * Output image
     */
    public function outputImage () {

        $builder = new CaptchaBuilder;
        $builder->build();

        $_SESSION['captcha_phrase'] = $builder->getPhrase(4);

        header('Content-type: image/jpeg');
        $builder->output();
    }

    /**
     * Check captcha
     */
    function validatePOST (): bool {
        if (mb_strtolower($_POST['captcha']) != mb_strtolower($_SESSION['captcha_phrase'])) {
            return false;
        }
        return true;

    }
}