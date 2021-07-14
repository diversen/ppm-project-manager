<?php declare(strict_types=1);

namespace Pebble;

use Pebble\Special;

class Template
{

    /**
     * Get output from a template 
     */
    public static function getOutput (string $template, array $vars = []) {
        
        ob_start();

        self::render($template, $vars);

        $content = ob_get_clean();

        return $content;
    }

    /**
     * Render a template using a template path and some variables
     * Any special entity is encoded on strings and numeric values. 
     * Set options['raw'] and no encoding will occur
     */
    public static function render ($template_path, $variables = [], array $options = []) {
        if (!isset($options['raw'])) {
            $variables = Special::encodeAry($variables);
        }
        
        extract($variables);

        require($template_path);
    }
}

