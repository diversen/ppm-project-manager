<?php declare(strict_types=1);

namespace Pebble;

use Gregwar\Captcha\CaptchaBuilder;

/**
 * Wrapper around Captcha
 */
class Captcha {


    /**
     * @return Gregwar\Captcha\CaptchaBuilder;
     */
    public function getBuilder() {
        $builder = new CaptchaBuilder;
        $builder->build();

        $_SESSION['captcha_phrase'] = $builder->getPhrase(4);

        return $builder;
    }

    /**
     * Output image
     */
    public function outputImage () {

        $builder = $this->getBuilder();        
        header('Content-type: image/jpeg');

        $builder->output();
    }

    /**
     * Check captcha
     */
    function validate ($phrase): bool {
        if (mb_strtolower($phrase) != mb_strtolower($_SESSION['captcha_phrase'])) {
            return false;
        }
        return true;

    }
}