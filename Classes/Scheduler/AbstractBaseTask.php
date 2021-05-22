<?php

namespace WEBcoast\VersatileCrawler\Scheduler;


use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

abstract class AbstractBaseTask extends AbstractTask
{
    protected function getLanguageService(): LanguageService
    {
        return parent::getLanguageService() ?? LanguageService::create('default');
    }
}
