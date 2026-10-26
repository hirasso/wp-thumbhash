<?php

return [
    'root' => [
        'name' => 'hirasso/wp-thumbhash',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => 'e08acab6c8257aa9b4db300b70ded5b2f5b40088',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__.'/../',
        'aliases' => [
        ],
        'dev' => true,
    ],
    'versions' => [
        'srwiez/thumbhash' => [
            'pretty_version' => 'v1.6.0',
            'version' => '1.6.0.0',
            'reference' => 'ef50e204c4f3adbea23e1ab8b70fd677569a3824',
            'type' => 'library',
            'install_path' => __DIR__.'/../srwiez/thumbhash',
            'aliases' => [
            ],
            'dev_requirement' => true,
        ],
        'yahnis-elsts/plugin-update-checker' => [
            'pretty_version' => 'v5.7',
            'version' => '5.7.0.0',
            'reference' => '275a96a2a18d03c34c87f35cb68673c8c49ac3b1',
            'type' => 'library',
            'install_path' => __DIR__.'/../yahnis-elsts/plugin-update-checker',
            'aliases' => [
            ],
            'dev_requirement' => true,
        ],
    ],
];
