<?php

namespace WEBcoast\VersatileCrawler\Scheduler;


use TYPO3\CMS\Scheduler\Task\AbstractTask;

abstract class AbstractBaseTask extends AbstractTask
{
    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
