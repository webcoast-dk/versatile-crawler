<?php

declare(strict_types=1);

namespace WEBcoast\VersatileCrawler\Utility;

use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaUtility
{
    public function getLanguageTcaItems(&$params)
    {
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($params['row']['pid']);
        foreach ($site->getLanguages() as $language) {
            $params['items'][] = [$language->getTitle(), $language->getLanguageId()];
        }
    }
}
