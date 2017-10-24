<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'type' => 'type',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'disabled',
        ],
        'searchFields' => 'title',
        'default_sortby' => 'title ASC'
    ],
    'columns' => [
        'title' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'maxLength' => 100,
                'eval' => 'trim,required'
            ]
        ],
        'domain' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.domain',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_domain',
                'items' => [
                    ['', 0]
                ]
            ]
        ],
        'base_url' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.base_url',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'maxLength' => 200,
                'eval' => 'trim'
            ]
        ],
        'type' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', ''],
                    ['LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.type.pageTree', 'pageTree'],
                    ['LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.type.records', 'records'],
                    ['LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.type.meta', 'meta']
                ]
            ]
        ],
        'levels' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.levels',
            'config' => [
                'type' => 'input',
                'eval' => 'int',
                'range' => [
                    'lower' => 0,
                    'upper' => 10
                ],
                'slider' => [
                    'step' => 1,
                    'width' => 110
                ],
                'default' => 0
            ]
        ],
        'exclude_pages_with_configuration' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.exclude_pages_with_configuration',
            'config' => [
                'type' => 'check',
                'default' => true
            ]
        ],
        'languages' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.languages',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'special' => 'languages',
            ]
        ],
        'table_name' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.table_name',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'tables',
            ]
        ],
        'record_storage_page' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.record_storage_page',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'minitems' => 1,
                'wizards' => [
                    'suggest' => [
                        'type' => 'suggest'
                    ]
                ]
            ]
        ],
        'record_storage_page_recursive' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.record_storage_page_recursive',
            'config' => [
                'type' => 'input',
                'eval' => 'int',
                'range' => [
                    'lower' => 0,
                    'upper' => 10
                ],
                'slider' => [
                    'step' => 1,
                    'width' => 11
                ],
                'default' => 0
            ]
        ],
        'record_title_field' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.record_title_field',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'maxLength' => 200,
                'eval' => 'trim'
            ]
        ],
        'record_content_fields' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.record_content_fields',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'maxLength' => 2000,
                'eval' => 'trim'
            ]
        ],
        'configurations' => [
            'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.configurations',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_versatilecrawler_domain_model_configuration',
                'MM' => 'tx_versatilecrawler_domain_model_configuration_mm',
                'size' => 5,
                'autoSizeMax' => 20
            ]
        ]
    ],
    'types' => [
        '0' => [
            'showitem' => 'title,type'
        ],
        'pageTree' => [
            'showitem' => 'title,type,domain,base_url,levels,exclude_pages_with_configuration,languages'
        ],
        'records' => [
            'showitem' => 'title,type,domain,base_url,table_name,records_storage_page,record_storage_page_recursive,languages'
        ],
        'meta' => [
            'showitem' => 'title,type,configurations'
        ]
    ]
];
