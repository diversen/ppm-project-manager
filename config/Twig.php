<?php

/**
 * Production settings for twig
 */
use Pebble\Path;

return [
    'cache' => Path::getBasePath() . '/cache/twig',
    'auto_reload' => false,
    'debug' => false,
];
