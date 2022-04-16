<?php

return [
    // languages enabled
    'enabled' => [
        'en',
        'da',
        'de',
    ],

    // default language 
    'default' => 'en',
    
    // Directory were translation dir ('lang') exist 
    'translate_base_dir' => 'src',

    // Directory were js translation is exported to
    'translate_base_dir_js' => 'www/js/lang', 

    // For doing auto translations of languages enabled
    'google_application_credentials' => 'config-locale/pebble-xxx.json',
];
