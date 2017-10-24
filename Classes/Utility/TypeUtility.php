<?php

namespace WEBcoast\VersatileCrawler\Utility;


use TYPO3\CMS\Core\SingletonInterface;

class TypeUtility implements SingletonInterface
{
    /**
     * @param string $type             The type value, that is to be used in the TCA.
     * @param string $class            The class name for the queue and crawl methods.
     */
    public static function registerType($type, $class)
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['versatile_crawler']['types'][$type] = $class;
    }

    public static function getClassForType($type)
    {
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['versatile_crawler']['types'][$type])) {
            throw new \RuntimeException(sprintf('There is no type "%s" registered', $type));
        }

        return $GLOBALS['TYPO3_CONF_VARS']['EXT']['versatile_crawler']['types'][$type];
    }
}
