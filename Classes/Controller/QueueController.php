<?php

namespace WEBcoast\VersatileCrawler\Controller;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\VersatileCrawler\Crawler\QueueInterface;
use WEBcoast\VersatileCrawler\Queue\Manager;
use WEBcoast\VersatileCrawler\Utility\TypeUtility;

class QueueController
{

    const CONFIGURATION_TABLE = 'tx_versatilecrawler_domain_model_configuration';

    /**
     * @var \WEBcoast\VersatileCrawler\Queue\Manager
     */
    protected $queueManager;

    /**
     * CrawlerController constructor.
     */
    public function __construct()
    {
        $this->queueManager = GeneralUtility::makeInstance(Manager::class);
    }

    /**
     * @param array $configurations
     *
     * @return boolean
     */
    public function fillQueue($configurations)
    {
        $queueManager = GeneralUtility::makeInstance(Manager::class);
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(
            self::CONFIGURATION_TABLE
        );
        $result = true;
        foreach ($configurations as $configurationUid) {
            $configurationResult = $connection->select(['*'], self::CONFIGURATION_TABLE, ['uid' => $configurationUid]);
            $configuration = $configurationResult->fetch();
            $className = TypeUtility::getClassForType($configuration['type']);
            $crawler = GeneralUtility::makeInstance($className);
            if (!$crawler instanceof QueueInterface) {
                throw new \RuntimeException(
                    sprintf(
                        'The registered crawler class "%s" must implement "%s"',
                        $className,
                        QueueInterface::class
                    )
                );
            }
            $result = $result && $crawler->fillQueue($configuration);
            // delete old queue entries
            $queueManager->cleanUpOldItems($GLOBALS['EXEC_TIME'], $configuration['uid']);
        }

        return $result;
    }
}