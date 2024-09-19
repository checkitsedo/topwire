<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'topwire',
    'description' => 'Turbo for TYPO3',
    'version' => '10.0.0',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
            'php' => '7.4.0-7.4.99',
            't3kit' => '10.0.0-10.0.99'
        ],
        'conflicts' => [
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => '',
    'author_email' => '',
    'author_company' => '',
    'autoload' => [
        'psr-4' => [
            'Topwire\\Topwire\\' => 'Classes'
        ],
    ],
];
