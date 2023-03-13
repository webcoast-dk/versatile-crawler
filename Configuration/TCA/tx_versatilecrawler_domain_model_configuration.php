<?php

return (function() {
    $tca = [
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
                        ['LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.type.files', 'files'],
                        ['LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.type.meta', 'meta']
                    ]
                ]
            ],
            'indexing_configuration' => [
                'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.indexing_configuration',
                'config' => [
                    'type' => 'group',
                    'internal_type' => 'db',
                    'allowed' => 'index_config',
                    'minitems' => 0,
                    'maxitems' => 1,
                    'wizards' => [
                        'suggest' => [
                            'type' => 'suggest'
                        ]
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
                    'minitems' => 1
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
                        'width' => 110
                    ],
                    'default' => 1
                ]
            ],
            'query_string' => [
                'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.query_string',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim',
                    'default' => ''
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
            ],
            'file_storages' => [
                'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.file_storages',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'foreign_table' => 'sys_file_storage',
                    'MM' => 'tx_versatilecrawler_domain_model_configuration_file_storage_mm',
                    'size' => 5,
                    'autoSizeMax' => 20,
                    'minitems' => 1
                ]
            ],
            'file_extensions' => [
                'label' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:tx_versatilecrawler_domain_model_configuration.file_extensions',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim',
                    'default' => ''
                ]
            ],
        ],
        'types' => [
            '0' => [
                'showitem' => 'title,type'
            ],
            'pageTree' => [
                'showitem' => 'title,type,indexing_configuration,base_url,levels,exclude_pages_with_configuration,languages'
            ],
            'records' => [
                'showitem' => 'title,type,indexing_configuration,base_url,table_name,record_storage_page,record_storage_page_recursive,query_string,languages'
            ],
            'files' => [
                'showitem' => 'title,type,indexing_configuration,file_storages,file_extensions,languages'
            ],
            'meta' => [
                'showitem' => 'title,type,indexing_configuration,configurations'
            ]
        ]
    ];

    if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version()) >= \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger('11.0.0')) {
        unset($tca['columns']['languages']['config']['special']);
        $tca['columns']['languages']['config']['itemsProcFunc'] = \WEBcoast\VersatileCrawler\Utility\TcaUtility::class . '->getLanguageTcaItems';
    }

    return $tca;
})();
