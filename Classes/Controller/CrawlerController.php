<?php

namespace WEBcoast\VersatileCrawler\Controller;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\VersatileCrawler\Crawler\CrawlerInterface;
use WEBcoast\VersatileCrawler\Queue\Manager;
use WEBcoast\VersatileCrawler\Utility\TypeUtility;

class CrawlerController
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
     * @param $numberOfItemsPerRun
     *
     * @return boolean
     */
    public function processQueue($numberOfItemsPerRun)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(
            self::CONFIGURATION_TABLE
        );
        $pendingItems = $this->queueManager->getPendingItems($numberOfItemsPerRun);
        $result = true;
        $queueManager = GeneralUtility::makeInstance(Manager::class);

        foreach ($pendingItems as $itemArray) {
            $item = $queueManager->getFromRecord($itemArray);
            $configurationResult = $connection->select(
                ['*'],
                self::CONFIGURATION_TABLE,
                ['uid' => $itemArray['configuration']]
            );
            $configuration = $configurationResult->fetch();
            $className = TypeUtility::getClassForType($configuration['type']);
            $crawler = GeneralUtility::makeInstance($className);
            if (!$crawler instanceof CrawlerInterface) {
                throw new \RuntimeException(
                    sprintf(
                        'The registered crawler class "%s" must implement "%s"',
                        $className,
                        CrawlerInterface::class
                    )
                );
            }
            $result = $result && $crawler->processQueueItem($item, $configuration);
            $queueManager->updateState($item);
        }

        return $result;
    }
}