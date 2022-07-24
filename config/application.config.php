<?php
return array(
    'modules' => array(
        'Application'
    ),
    'module_listener_options' => array(
        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php',
            'public/lincut.config.php',
        ),
        'module_paths' => array(
            './module',
            './vendor',
        ),
    ),
);
