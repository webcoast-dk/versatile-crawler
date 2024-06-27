<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
    'tx_versatilecrawler_domain_model_configuration'
);

$iconRegistry = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
    'tcarecords-tx_versatilecrawler_domain_model_configuration-default',
    TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:versatile_crawler/Resources/Public/Icons/tx_versatilecrawler_domain_model_configuration.svg']
);
