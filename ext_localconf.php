<?php

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\WEBcoast\VersatileCrawler\Scheduler\QueueTask::class] = [
    'extension' => 'versatile_crawler',
    'title' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:scheduler.queueTask',
    'description' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:scheduler.queueTask.description',
    'additionalFields' => \WEBcoast\VersatileCrawler\Scheduler\QueueTaskAdditionalFieldProvider::class
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\WEBcoast\VersatileCrawler\Scheduler\ProcessTask::class] = [
    'extension' => 'versatile_crawler',
    'title' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:scheduler.processTask',
    'description' => 'LLL:EXT:versatile_crawler/Resources/Private/Language/locallang_backend.xlf:scheduler.processTask.description',
    'additionalFields' => \WEBcoast\VersatileCrawler\Scheduler\ProcessTaskAdditionalFieldProvider::class
];

WEBcoast\VersatileCrawler\Utility\TypeUtility::registerType(
    'pageTree',
    WEBcoast\VersatileCrawler\Crawler\PageTree::class
);
WEBcoast\VersatileCrawler\Utility\TypeUtility::registerType(
    'records',
    WEBcoast\VersatileCrawler\Crawler\Records::class
);
WEBcoast\VersatileCrawler\Utility\TypeUtility::registerType(
    'meta',
    WEBcoast\VersatileCrawler\Crawler\Meta::class
);
WEBcoast\VersatileCrawler\Utility\TypeUtility::registerType(
    'files',
    WEBcoast\VersatileCrawler\Crawler\Files::class
);
