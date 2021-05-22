<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Versatile Crawler',
    'description' => 'A versatile and extendable crawler',
    'version' => '1.1.1',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
        ],
        'conflicts' => [
            'crawler' => ''
        ],
        'suggests' => [
        ],
    ],
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'author' => 'Thorben Nissen',
    'author_email' => 'thorben@webcoast.dk',
    'author_company' => '',
    'autoload' => [
        'psr-4' => [
            'WEBcoast\\VersatileCrawler\\' => 'Classes'
        ]
    ]
];
