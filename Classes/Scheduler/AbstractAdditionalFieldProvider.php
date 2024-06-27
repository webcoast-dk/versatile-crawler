<?php

declare(strict_types=1);

namespace WEBcoast\VersatileCrawler\Scheduler;

use TYPO3\CMS\Core\Localization\LanguageService;

abstract class AbstractAdditionalFieldProvider extends \TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider
{
    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? LanguageService::create('default');
    }
}
